<?php

namespace Render\PageUrlHelper;

/**
 * Class SimplePageUrlHelper
 * Simple PageUrlHelper based on an array of page urls and a fixed CSS URL
 *
 * @package Render\PageUrlHelper
 */
class SimplePageUrlHelper extends AbstractPageUrlHelper
{

  /**
   * @var array
   */
  private $pageUrls;

  /**
   * @var string
   */
  private $currentCssUrl;


  /**
   * @param array  $pageUrls      array('PAGE-ID' => 'URL', ...)
   * @param string $currentPageId id of the current page
   * @param string $currentCssUrl URL path to the pages CSS file
   * @param string $pageUrlPrefix Prefix for the Page URLs
   */
  public function __construct(array $pageUrls, $currentPageId, $currentCssUrl, $pageUrlPrefix = '')
  {
    $this->pageUrls = $pageUrls;
    $this->currentPageId = $currentPageId;
    $this->currentCssUrl = $currentCssUrl;
    $this->pageUrlPrefix = $pageUrlPrefix;
  }


  /**
   * Page URL
   *
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return null|string
   * @throws PageUrlNotAvailable
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl)
  {
    if (!isset($this->pageUrls[$pageId])) {
      throw new PageUrlNotAvailable('PageUrlNotAvailable for ' . $pageId);
    }
    $url = $this->pageUrlPrefix . $this->pageUrls[$pageId];
    return $this->createUrlWithQuery($url, $parameters, $absoluteUrl);
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
    return $this->currentCssUrl;
  }
}
