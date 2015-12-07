<?php
namespace Cms\Business;

use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;
use Seitenbau\Registry as Registry;
use Cms\Exception as CmsException;
use Cms\Request\Base as CmsRequestBase;
use Render\RequestHelper\HttpRequestInterface;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaCDNHelper\MediaResponseFactory;
use Render\MediaUrlHelper\IMediaUrlHelper;
use Render\MediaContext;
use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Cms\Render\MediaUrlHelper\CmsCDNMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\SecureFileValidationHelper;
use Render\IconHelper\SimpleIconHelper;
use Render\ImageToolFactory\SimpleImageToolFactory;

/**
 * Stellt die Business-Logik fuer Cdn zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Cdn extends Base\Service
{
  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @return string
   */
  public function getBuildfilePath($websiteId, $buildId)
  {
    if (!$this->getService('Website')->existsWebsiteAlready($websiteId)) {
      throw new CmsException('602', __METHOD__, __LINE__);
    }
    return $this->getBusiness('Builder')->getWebsiteBuildFilePath($websiteId, $buildId);
  }

  /**
   * Gibt den Dateipfad zum Vorschaubild des angegebenen Screenshots zurueck
   *
   * Ist kein Vorschaubild angelegt, so wird versucht eines anzulegen
   *
   * Pfad-Aufbau: /[screen-directory]/[website-id]/[screen-typ]/[id]/[datei]
   *
   * @param string $websiteId
   * @param string $id
   * @param string $type
   * @param array $options
   * @return string|false
   */
  public function getScreenStreamFilePath($websiteId, $id, $type, array $options = array())
  {
    $options['width']   = (isset($options['width']))
                        ? (int) $options['width']
                        : (int) Registry::getConfig()->screens->thumbnail->width;
    $options['height']  = (isset($options['height']))
                        ? (int) $options['height']
                        : (int) Registry::getConfig()->screens->thumbnail->height;

    return $this->getBusiness('Screenshot')->getScreenshotFilePath($websiteId, $id, $type, $options);
  }

  public function getScreenFileName($id, $type)
  {
    return $id . '.' . Registry::getConfig()->screens->filetype;
  }

  public function getDefaultScreenshot()
  {
    return $this->getBusiness('Screenshot')->getDefaultScreenshot();
  }

  /**
   * Bereinigt den uebergebenen Dateinamen, damit dieser im Response
   * fuer den Browser verstaendlich ankommt
   *
   * @param  string $fileName
   * @return string
   */
  public function cleanFileNameForResponse($fileName)
  {
    $structureNameSearch = array('/[\x{00d6}]/u',       // 'Oe'
                                 '/[\x{00f6}]/u',       // 'oe'
                                 '/[\x{00c4}]/u',       // 'Ae'
                                 '/[\x{00e4}]/u',       // 'ae'
                                 '/[\x{00dc}]/u',       // 'Ue'
                                 '/[\x{00fc}]/u',       // 'ue'
                                 '/[\x{00df}]/u',       // 'ss'
                                 '/:/u',                // '-'
                                 '/[^0-9a-z _\-.]/i',   // ' '
                                 '/[ ]{2,}/i',          // ' '
                           );
    $structureNameReplace = array('Oe',
                                  'oe',
                                  'Ae',
                                  'ae',
                                  'Ue',
                                  'ue',
                                  'ss',
                                  '-',
                                  ' ',
                                  ' ',
                            );
    return preg_replace($structureNameSearch, $structureNameReplace, $fileName);
  }

  /**
   * @param HttpRequestInterface $httpRequest
   *
   * @return ResponseInterface
   */
  public function createMediaResponse(HttpRequestInterface $httpRequest)
  {
    $mediaCache = $this->createMediaCache();
    $mediaValidationHelper = new SecureFileValidationHelper($mediaCache, false);
    $mediaUrlHelper = $this->createMediaUrlHelper($mediaValidationHelper);
    $mediaRequest = $mediaUrlHelper->getMediaRequest($httpRequest);
    $mediaContext = $this->createMediaContext(
        $mediaUrlHelper,
        $mediaRequest->getWebsiteId()
    );
    $mediaResponseFactory = new MediaResponseFactory(
        $mediaContext,
        $mediaCache,
        $mediaValidationHelper
    );
    return $mediaResponseFactory->createResponse($httpRequest, $mediaRequest);
  }

  /**
   * @return MediaCache
   */
  protected function createMediaCache()
  {
    $mediaCacheBaseDirectory = Registry::getConfig()->media->cache->directory;
    return new MediaCache($mediaCacheBaseDirectory);
  }

  /**
   * @return CmsCDNMediaUrlHelper
   */
  protected function createMediaUrlHelper(ValidationHelperInterface $mediaValidationHelper)
  {
    $cdnUrl = Registry::getConfig()->server->url . '/cdn/get';
    return new CmsCDNMediaUrlHelper(
        $mediaValidationHelper,
        $cdnUrl,
        CmsRequestBase::REQUEST_PARAMETER
    );
  }

  /**
   * @param IMediaUrlHelper $mediaUrlHelper
   * @param string          $websiteId
   *
   * @return MediaContext
   */
  protected function createMediaContext(IMediaUrlHelper $mediaUrlHelper, $websiteId)
  {
    $mediaDirectory = Registry::getConfig()->media->files->directory;
    $mediaDirectory .= DIRECTORY_SEPARATOR . $websiteId;
    $mediaService = $this->getService('Media');
    $iconHelper = new SimpleIconHelper(
        Registry::getConfig()->file->types->icon->directory,
        'icon_fallback.png'
    );
    $mediaInfoStorage = new ServiceBasedMediaInfoStorage(
        $websiteId,
        $mediaDirectory,
        $mediaService,
        $mediaUrlHelper,
        $iconHelper
    );
    $imageToolFactory = new SimpleImageToolFactory(APPLICATION_PATH . '/../library');
    return new MediaContext($mediaInfoStorage, $imageToolFactory);
  }
}
