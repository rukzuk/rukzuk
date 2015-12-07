<?php
namespace Cms\Business;

use Cms\Business\Export as ExportBusiness;
use Cms\Exception as CmsException;
use Cms\Version as Version;
use Seitenbau\Registry as Registry;
use Seitenbau\Types\Boolean as Boolean;
use Seitenbau\File\TransferFactory as TransferFactory;
use Cms\Business\ContentUpdater as ContentUpdaterBusiness;
use Cms\Service\Import\ConflictException;
use Seitenbau\FileSystem as FS;

/**
 * Stellt die Business-Logik fuer Import zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\Import getService
 */

class Import extends Base\Service
{
  const IMPORT_FROM_FILE = 'IMPORT_FROM_FILE';
  const IMPORT_FROM_FILE_CONFLICT = 'IMPORT_FROM_FILE_CONFLICT';
  const IMPORT_FROM_URL = 'IMPORT_FROM_URL';
  const IMPORT_FROM_URL_CONFLICT = 'IMPORT_FROM_URL_CONFLICT';
  const IMPORT_FROM_LOCAL_FILE = 'IMPORT_FROM_LOCAL_FILE';
  const IMPORT_FROM_LOCAL_FILE_CONFLICT = 'IMPORT_FROM_LOCAL_FILE_CONFLICT';
  const IMPORT_CANCEL = 'IMPORT_CANCEL';
  const IMPORT_OVERWRITE = 'IMPORT_OVERWRITE';

  /**
   * @var ContentUpdaterBusiness
   */
  private $contentUpdaterBusiness;

  /**
   * @param  array  $templateIds
   * @param  array  $moduleIds
   * @param  array  $templateSnippetIds
   * @param  array  $mediaIds
   * @param  string $importId
   *
   * @return array
   */
  public function overwriteImport(
      array $templateIds,
      array $moduleIds,
      array $templateSnippetIds,
      array $mediaIds,
      $importId
  ) {
    $importData = $this->getService()->overwriteImport(
        $templateIds,
        $moduleIds,
        $templateSnippetIds,
        $mediaIds,
        $importId
    );
    $this->updateDataAfterImport($importData['websiteId'], $importData);
    return $importData;
  }

  /**
   * @param string $importId
   */
  public function cancelImport($importId)
  {
    $latchBusiness = new \Cms\Business\Import\Latch('Latch');
    $latchBusiness->unlatchImportFile($importId);
  }

  /**
   * Haendelt den Import einer Datei
   *
   * @param  string $uploadFilename
   * @param  string $websiteId
   * @param  string $allowedType
   *
   * @return array
   * @throws \Cms\Exception
   */
  public function importUploadFile($uploadFilename, $websiteId, $allowedType)
  {
    $config = Registry::getConfig();
    $importDirectory = $config->import->directory;
    FS::createDirIfNotExists($importDirectory);

    $import = TransferFactory::getAdapter();
    $imports = $import->getFileInfo();

    if (isset($config->import->uploadFile) && isset($config->import->uploadFile->doNotRename)) {
      $renameUploadFile = new Boolean($config->import->uploadFile->doNotRename);
      $renameUploadFile = !$renameUploadFile->getValue();
    } else {
      $renameUploadFile = true;
    }
    if ($renameUploadFile) {
      $uploadFilename = basename($uploadFilename, '.zip') . '.P' . time() . '_' . rand(100000, 999999) . '.zip';
    }

    $import->addFilter('Rename', $uploadFilename);

    $import->setDestination($importDirectory);
    $import->receive();

    $importFile = $importDirectory . DIRECTORY_SEPARATOR . $uploadFilename;

    return $this->importFile($importFile, $websiteId, $allowedType);
  }

  /**
   * Haendelt den Import ueber eine Url
   *
   * @param string $url
   * @param string $websiteId
   * @param string $allowedType
   *
   * @throws \Cms\Exception
   * @return array
   */
  public function importUrl($url, $websiteId, $allowedType)
  {
    $importConfig = Registry::getConfig()->import;

    // Import ueber Url aktiv?
    if (!isset($importConfig->url->enabled)
      || !$importConfig->url->enabled
    ) {
      throw new CmsException('18', __METHOD__, __LINE__);
    }

    // Import von der angegebenen Url erlaubt?
    $importAllowed = false;
    if (isset($importConfig->url->allowed_urls)) {
      $allowedUrls = $importConfig->url->allowed_urls->toArray();
      foreach ($allowedUrls as $allowedUrlRegExp) {
        if (preg_match($allowedUrlRegExp, $url)) {
          $importAllowed = true;
          break;
        }
      }
    }
    if (!$importAllowed) {
      // Import nicht erlaubt
      throw new CmsException('19', __METHOD__, __LINE__, array(
        'url' => $url
      ));
    }

    // Import-Datei herunterladen
    $importDirectory = $importConfig->directory;
    $requestConfig = $importConfig->url->request;
    $importFile = $importDirectory .
      DIRECTORY_SEPARATOR . 'import_download.P' . time() . '_' . rand(100000, 999999) . '.zip';

    $FH_DOWNLOAD = @fopen($importFile, 'w');
    if (!$FH_DOWNLOAD) {
      // Fehler
      $errors = error_get_last();
      throw new CmsException('20', __METHOD__, __LINE__, array(
        'file' => $importFile,
        'error_type' => $errors['type'],
        'error_message' => $errors['message']
      ));
    }

    // Einlesen ueber curl
    if (function_exists("curl_init") && function_exists("curl_setopt") &&
      function_exists("curl_exec") && function_exists("curl_close") &&
      $ch = curl_init()
    ) {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_MAXREDIRS, $requestConfig->max_redirects);
      curl_setopt($ch, CURLOPT_TIMEOUT, $requestConfig->timeout);
      curl_setopt($ch, CURLOPT_FILE, $FH_DOWNLOAD);
      @curl_exec($ch);
      if (curl_errno($ch)) {
        fclose($FH_DOWNLOAD);
        // Fehler
        throw new CmsException('21', __METHOD__, __LINE__, array(
          'url' => $url,
          'error_no' => curl_errno($ch),
          'error_message' => curl_error($ch)
        ));
      }
      curl_close($ch);
    } // Einlesen ueber Stream
    elseif (function_exists('stream_context_create') &&
      function_exists('stream_copy_to_stream')
    ) {
      $opts = array(
        'http' => array(
          'method' => 'GET',
          'max_redirects' => $requestConfig->max_redirects,
          'timeout' => $requestConfig->timeout
        ),
        'ssl' => array(
          'verify_peer' => false
        )
      );
      $context = stream_context_create($opts);
      $FH_URL = fopen($url, 'r', false, $context);
      if (!$FH_URL) {
        // Fehler
        fclose($FH_DOWNLOAD);
        $errors = error_get_last();
        throw new CmsException('21', __METHOD__, __LINE__, array(
          'url' => $url,
          'error_no' => $errors['type'],
          'error_message' => $errors['message']
        ));
      }
      $bytes = stream_copy_to_stream($FH_URL, $FH_DOWNLOAD);
      fclose($FH_URL);
      if ($bytes <= 0) {
        // Fehler
        fclose($FH_DOWNLOAD);
        $errors = error_get_last();
        throw new CmsException('21', __METHOD__, __LINE__, array(
          'url' => $url,
          'error_no' => -1,
          'error_message' => 'Downloaded file has no size'
        ));
      }
    }

    // Datei schreiben und schliessen
    fflush($FH_DOWNLOAD);
    fclose($FH_DOWNLOAD);

    return $this->importFile($importFile, $websiteId, $allowedType);
  }

  /**
   * @param string      $localId
   * @param string      $websiteId
   * @param string      $allowedType
   * @param string|null $websiteName
   *
   * @return array
   * @throws CmsException
   */
  public function importLocalFiles($localId, $websiteId, $allowedType, $websiteName)
  {
    $baseDirectory = realpath(Registry::getConfig()->import->local_files->directory);
    $localImportDirectory = realpath(FS::joinPath($baseDirectory, $localId));
    if (0 !== strpos($localImportDirectory, $baseDirectory)) {
      throw new CmsException('10', __METHOD__, __LINE__, array(
        'localId' => $localId,
        'detail' => 'invalid local id',
      ));
    }
    return $this->importFromDirectory($localImportDirectory, $websiteId, $allowedType, $websiteName);
  }

  /**
   * @param string  $importFile
   * @param string  $websiteId
   * @param string  $allowedType
   *
   * @throws \Cms\Service\Import\ConflictException
   * @throws \Exception
   * @throws \Exception
   * @return array
   */
  protected function importFile($importFile, $websiteId, $allowedType)
  {
    try {
      $importData = $this->getService()->import(
          $websiteId,
          $importFile,
          $allowedType
      );
      $this->updateDataAfterImport($importData['websiteId'], $importData);
    } catch (ConflictException $e) {
      $this->cleanupImport($importFile);
      throw $e;
    } catch (\Exception $e) {
      $this->removeImportFiles($importFile);
      throw $e;
    }
    $this->cleanupImport($importFile);
    return $importData;
  }

  /**
   * @param string      $importDirectory
   * @param string      $websiteId
   * @param string      $allowedType
   * @param string|null $websiteName
   *
   * @return array
   * @throws CmsException
   */
  protected function importFromDirectory($importDirectory, $websiteId, $allowedType, $websiteName)
  {
    $importData = $this->getService()->importFromDirectory(
        $websiteId,
        $importDirectory,
        $allowedType,
        $websiteName
    );
    $this->updateDataAfterImport($importData['websiteId'], $importData);
    return $importData;
  }

  /**
   * @param string $websiteId
   * @param array  $importData
   */
  protected function updateDataAfterImport($websiteId, $importData)
  {
    $this->getContentUpdaterBusiness()->updateAllContentsOfWebsite($websiteId);
    if (isset($importData['pages'])) {
      $this->updatePageGlobals($websiteId, $importData['pages']);
    }
  }

  /**
   * @param string $websiteId
   * @param array  $pages
   */
  protected function updatePageGlobals($websiteId, $pages)
  {
    $pageBusiness = $this->getBusiness('Page');
    foreach ($pages as $page) {
      if (isset($page['id'])) {
        $pageBusiness->updateGlobalVars($page['id'], $websiteId);
      }
    }
  }

  /**
   * @param string  $importFile
   * @param boolean $onlyUnzipDirectory remove only the unzip directory
   */
  public function removeImportFiles($importFile, $onlyUnzipDirectory = false)
  {
    return $this->getService()->removeImportFiles($importFile, $onlyUnzipDirectory);
  }

  protected function cleanupImport($importFile)
  {
    $config = Registry::getConfig();
    $importDeleteAfterImport = new Boolean($config->import->delete->after->import);
    $importDeleteAfterImport = $importDeleteAfterImport->getValue();
    if ($importDeleteAfterImport) {
      $this->removeImportFiles($importFile);
    }
  }

  /**
   * @return ContentUpdater
   */
  protected function getContentUpdaterBusiness()
  {
    if (!isset($this->contentUpdaterBusiness)) {
      $this->contentUpdaterBusiness = new ContentUpdaterBusiness('ContentUpdater');
    }
    return $this->contentUpdaterBusiness;
  }
}
