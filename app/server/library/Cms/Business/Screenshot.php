<?php
namespace Cms\Business;

use Seitenbau\Registry as Registry;
use Cms\Business\Screenshot\Url as ScreenshotUrl;
use Seitenbau\Log as SbLog;
use Seitenbau\FileSystem as FS;
use Seitenbau\Image as ImageTool;

/**
 * Stellt die Business-Logik fuer Screenshot zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Screenshot extends Base\Service
{
  const SCREEN_TYPE_PAGE      = 'page';
  const SCREEN_TYPE_TEMPLATE  = 'template';
  const SCREEN_TYPE_WEBSITE   = 'website';
  
  public function shootPage($websiteId, $pageId)
  {
    if (!$this->getService()->isActive()) {
      return false;
    }
    
    $this->getBusiness('Website')->getById($websiteId);
    $page = $this->getBusiness('Page')->getById($pageId, $websiteId);
    
    $shootId = $this->getService()->calculateShootId(
        $websiteId,
        'page',
        $pageId,
        $page->getLastUpdate()
    );

    $screenshotUrl = new ScreenshotUrl($websiteId, 'page', $pageId);

    return $this->getService()->shootPage($websiteId, $pageId, $shootId, $screenshotUrl);
  }

  public function shootTemplate($websiteId, $templateId)
  {
    if (!$this->getService()->isActive()) {
      return false;
    }
    
    $this->getBusiness('Website')->getById($websiteId);
    $template = $this->getBusiness('Template')->getById($templateId, $websiteId);
    
    $shootId = $this->getService()->calculateShootId(
        $websiteId,
        'template',
        $templateId,
        $template->getLastUpdate()
    );

    $screenshotUrl = new ScreenshotUrl($websiteId, 'template', $templateId);

    return $this->getService()->shootTemplate($websiteId, $templateId, $shootId, $screenshotUrl);
  }

  /**
   * @return string
   */
  public function getDefaultScreenshot()
  {
    return FS::joinPath(Registry::getConfig()->images->directory, 'pixel.gif');
  }

  /**
   * @param   string $websiteId
   * @param   string $id
   * @param   string $type
   * @param   array  $option
   * @return  string
   */
  public function getScreenshotFilePath($websiteId, $id, $type, array $options = array())
  {
    if (!$this->getService()->isActive()) {
      return;
    }
    
    if ($type == self::SCREEN_TYPE_WEBSITE) {
      list($type, $id)= $this->getItemTypeAndIdForWebsiteScreenshot($websiteId);
      if ($id == false) {
        return;
      }
    }

    $subdirType = $this->getPathByType($type);
    if ($subdirType == false) {
      return;
    }

    $relativeDir = FS::joinPath($websiteId, $subdirType);
    $originFile = FS::joinPath(
        Registry::getConfig()->screens->directory,
        $relativeDir,
        sprintf("%s.%s", $id, Registry::getConfig()->screens->filetype)
    );

    // Orginal Screen nicht vorhanden -> Screen erstellen
    if (!file_exists($originFile) || !$this->isScreenshotFileValid($originFile, $type, $id, $websiteId)) {
      try {
        switch ($type) {
          case self::SCREEN_TYPE_PAGE:
              $screenExists = $this->shootPage($websiteId, $id);
                break;
          case self::SCREEN_TYPE_TEMPLATE:
              $screenExists = $this->shootTemplate($websiteId, $id);
                break;
          default:
                return;
            break;
        }
      } catch (\Cms\Exception $logOnly) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
        return;
      }

      if ($screenExists == false || !file_exists($originFile)) {
        return;
      }
    }
    
    if (isset($options['width']) || isset($options['height'])) {
      return $this->modifyScreenhot($websiteId, $id, $type, $originFile, $relativeDir, $options);
    }
    
    return $originFile;
  }

  /**
   * @param   string $websiteId
   * @param   string $id
   * @param   string $type
   * @param   string $originFile
   * @param   string $relativeDir
   * @param   array  $option
   * @return  string
   */
  protected function modifyScreenhot($websiteId, $id, $type, $originFile, $relativeDir, $options)
  {
    $width  = (int)$options['width'];
    $height = (int)$options['height'];
    
    $cacheFilePath = FS::joinPath(
        Registry::getConfig()->screens->cache->directory,
        $relativeDir,
        $id
    );
    $cacheFile = FS::joinPath(
        $cacheFilePath,
        sprintf("%dx%d.%s", $width, $height, Registry::getConfig()->screens->filetype)
    );

    if (file_exists($cacheFile) && $this->isScreenshotFileValid($cacheFile, $type, $id, $websiteId)) {
      return $cacheFile;
    }

    FS::createDirIfNotExists($cacheFilePath, true);
    
    // Breite und Hoehe fuer crop berechnen
    list($originWidth, $originHeight) = getimagesize($originFile);
    $widthFaktor = $originWidth / $width;
    $heightFaktor = $originHeight / $height;
    if ($widthFaktor < $heightFaktor) {
      $cropWidth = (int) $originWidth;
      $cropHeight = (int) ($widthFaktor * $height);
    } else {
      $cropWidth = (int) ($heightFaktor * $width);
      $cropHeight = (int) $originHeight;
    }

    $bildBearbeitung = $this->getImageAdapter();
    $bildBearbeitung->setFile($originFile);
    $bildBearbeitung->crop(array('width' => $cropWidth, 'height' => $cropHeight, 'x' => 0, 'y' => 0));
    $bildBearbeitung->resize(array('width' => $width, 'height' => $height));
    $bildBearbeitung->quality(array('quality' => 100));
    if (!$bildBearbeitung->save($cacheFile)) {
      return;
    }
    
    return $cacheFile;
  }

  /**
   * @return Seitenbau\Image\Image
   */
  protected function getImageAdapter()
  {
    $imageAdapterConfig = null;
    $config = Registry::getConfig();
    if (isset($config->screenshot->imageAdapter)) {
      $imageAdapterConfig = $config->screenshot->imageAdapter;
    }
    return ImageTool::factory($imageAdapterConfig);
  }

  /**
   * @param string $type
   * @return string|false
   */
  protected function getPathByType($type)
  {
    switch ($type) {
      case self::SCREEN_TYPE_PAGE:
            return 'pages';
        break;
      case self::SCREEN_TYPE_TEMPLATE:
            return 'templates';
        break;
      default:
            return false;
    }
  }

  /**
   * Prueft, ob die angegebene Cache-Datei des Screens aktuell ist
   *
   * Entschieden wird nach dem Timestamp der Datei und der letzten
   * Aktualisierung der entsprechenden Unit (Page, Template, Website)
   *
   * @param string $cacheFile Dateipfadangabe zur Cache-Datei
   * @param string $type  Typ der Unit
   * @param string $id  ID der Unit
   * @param string $websiteId ID der Website
   * @return boolean
   */
  protected function isScreenshotFileValid($cacheFile, $type, $id, $websiteId)
  {
    switch ($type) {
      case self::SCREEN_TYPE_PAGE:
      case self::SCREEN_TYPE_WEBSITE:
        $unit = $this->getBusiness('Page')->getById($id, $websiteId);
            break;
      case self::SCREEN_TYPE_TEMPLATE:
        $unit = $this->getBusiness('Template')->getById($id, $websiteId);
            break;
      default:
            return false;
    }

    if (filemtime($cacheFile) >= $unit->getLastUpdate()) {
      return true;
    }

    return false;
  }
  
  
  /**
   * returns the item type and item id for the website screenshot
   *
   * @param string $websiteId   ID der Website
   * @return array              array(type, id)
   */
  private function getItemTypeAndIdForWebsiteScreenshot($websiteId)
  {
    try {
      $pageId = $this->getBusiness('Website')->getFirstPageFromWebsite($websiteId);
      if (!empty($pageId)) {
        return array(self::SCREEN_TYPE_PAGE, $pageId);
      }
      $templates = $this->getBusiness('Template')->getAll($websiteId);
      if (is_array($templates) && count($templates) > 0) {
        return array(self::SCREEN_TYPE_TEMPLATE, $templates[0]->getId());
      }
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, SbLog::NOTICE);
      return false;
    }

    return false;
  }
}
