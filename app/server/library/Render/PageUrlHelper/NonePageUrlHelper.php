<?php

namespace Render\PageUrlHelper;

/**
 * Class NonePageUrlHelper
 * Stub (Fake) impl which returns fake urls
 *
 * @package Render\PageUrlHelper
 */
class NonePageUrlHelper extends AbstractPageUrlHelper
{
  /**
   * Page URL
   *
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl)
  {
    return $this->createUrlWithQuery('#pageUrl_' . $pageId, $parameters, false);
  }

  /**
   * Url of the currently rendered Page (or Template)
   *
   * @return string
   */
  public function getCurrentUrl()
  {
    return '#currentPageUrl';
  }

  /**
   * Url of the corresponding CSS file to this Page (or Template)
   *
   * @return string
   */
  public function getCurrentCssUrl()
  {
    return '#cssUrl';
  }
}
