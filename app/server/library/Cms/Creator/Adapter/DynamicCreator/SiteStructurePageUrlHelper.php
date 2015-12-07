<?php

namespace Cms\Creator\Adapter\DynamicCreator;

use Render\PageUrlHelper\AbstractPageUrlHelper;
use Render\PageUrlHelper\PageUrlNotAvailable;

/**
 * Class SiteStructurePageUrlHelper
 *
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class SiteStructurePageUrlHelper extends AbstractPageUrlHelper
{
  /**
   * @var string
   */
  private $pageUrlPrefix = '';

  /**
   * @param SiteStructure $siteStructure
   * @param string        $currentPageId
   * @param string        $pageUrlPrefix
   */
  public function __construct($siteStructure, $currentPageId, $pageUrlPrefix = '')
  {
    $this->currentPageId = $currentPageId;
    $this->siteStructure = $siteStructure;
    $this->pageUrlPrefix = $pageUrlPrefix;
  }

  /**
   * Url of a specific Page
   *
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   * @throws PageUrlNotAvailable
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl)
  {
    $url = $this->siteStructure->getPageUrl($pageId, false);

    if (is_null($url)) {
      throw new PageUrlNotAvailable();
    }
    return $this->createUrlWithQuery($this->pageUrlPrefix . $url, $parameters, $absoluteUrl);
  }

  /**
   * Url of the currently rendered Page (or Template)
   *
   * @return string
   */
  public function getCurrentUrl()
  {
    return $this->getPageUrl($this->currentPageId, array(), false);
  }

  /**
   * Url of the corresponding CSS file to this Page (or Template)
   *
   * @return string
   */
  public function getCurrentCssUrl()
  {
    /** @see CreatorStorage::getPageCssFileName() */
    return $this->pageUrlPrefix . 'files/css/' . md5($this->currentPageId) . '.css';
  }
}
