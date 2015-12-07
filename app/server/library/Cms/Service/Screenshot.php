<?php
namespace Cms\Service;

use Cms\Service\Base\Plain as PlainServiceBase;
use \Seitenbau\Registry;
use \Seitenbau\Screenshot as SBScreenshot;

/**
 * Screen service
 *
 * @package      Cms
 * @subpackage   Service
 */

class Screenshot extends PlainServiceBase
{
  private $screenshotTool = null;
  private $active = false;

  public function init()
  {
    // Screenshot activ?
    if (Registry::getConfig()->screens->activ == 'yes'
        || Registry::getConfig()->screens->activ === true
        || Registry::getConfig()->screens->activ == '1') {
    // Aktiv
      $this->active = true;

      $this->screenshotTool = SBScreenshot::factory(
          Registry::getConfig()->screens->type,
          Registry::getConfig()->screens
      );
    } else {
      // Nicht activ
      $this->active = false;
    }

    parent::init();
  }

  /**
   * Ist das Screenshot-Tool aktiv
   *
   * @return boolean
   */
  public function isActive()
  {
    return ($this->active ? true : false);
  }

  /**
   * Erstellt einen Screenshot einer Page
   *
   * @param string $websiteId
   * @param string $pageId
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @return boolean
   */
  public function shootPage($websiteId, $pageId, $shootId, $screenshotUrl)
  {
    // Aktiv?
    if (!$this->isActive()) {
      return false;
    }

    $websiteDir = $this->getWebsiteDir($websiteId);
    $destinationFile = $websiteDir . DIRECTORY_SEPARATOR .
                       'pages' . DIRECTORY_SEPARATOR . $pageId . '.' .
                       $this->screenshotTool->getFiletype();

    return $this->screenshotTool->shoot($shootId, $screenshotUrl, $destinationFile);
  }

  /**
   * Erstellt einen Screenshot eines Templates
   *
   * @param string $websiteId
   * @param string $templateId
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @return boolean
   */
  public function shootTemplate($websiteId, $templateId, $shootId, $screenshotUrl)
  {
    // Aktiv?
    if (!$this->isActive()) {
      return false;
    }

    $websiteDir = $this->getWebsiteDir($websiteId);
    $destinationFile = $websiteDir . DIRECTORY_SEPARATOR .
                       'templates' . DIRECTORY_SEPARATOR . $templateId . '.' .
                       $this->screenshotTool->getFiletype();

    return $this->screenshotTool->shoot($shootId, $screenshotUrl, $destinationFile);
  }
  
  /**
   * Ermittelt die Id fuer einen Screenshot
   *
   * @param string $websiteId
   * @param string $type
   * @param string $id
   * @param string $timestamp
   * @return string
   */
  public function calculateShootId($websiteId, $type, $id, $timestamp)
  {
    return md5(Registry::getBaseUrl().'/'.$websiteId.'/'.$type.'/'.$id.'/'.$timestamp);
  }
  
  /**
   * Gibt das Website Verzeichnis fuer die Screens zurueck
   * Ist es nicht vorhanden, so wird es angelegt
   *
   * @param string $websiteId
   * @return string
   */
  private function getWebsiteDir($websiteId)
  {
    $websiteDir = $this->screenshotTool->getDirectory() . DIRECTORY_SEPARATOR .
      $websiteId;

    if (!\is_dir($websiteDir)) {
      $this->createWebsiteDir($websiteId);
    }

    return $websiteDir;
  }

  /**
   * Erstellt das Website-Verzeichnis fuer die Screens samt noetiger Unterordner
   *
   * @param string $websiteId
   * @return string
   */
  private function createWebsiteDir($websiteId)
  {
    $websiteDir = $this->screenshotTool->getDirectory() . DIRECTORY_SEPARATOR .
      $websiteId;

    \mkdir($websiteDir);
    \mkdir($websiteDir . DIRECTORY_SEPARATOR . 'pages');
    \mkdir($websiteDir . DIRECTORY_SEPARATOR . 'templates');

    return ;
  }
}
