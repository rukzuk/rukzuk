<?php
namespace Render\PageUrlHelper;

/**
 * Interface IPageUrlHelper
 * URLs for Pages, Templates and CSS Files (all generated code)
 */
interface IPageUrlHelper
{
  /**
   * Url of a specific Page
   *
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl);

  /**
   * Url of the currently rendered Page (or Template)
   *
   * @return string
   */
  public function getCurrentUrl();

  /**
   * Url of the corresponding CSS file to this Page (or Template)
   *
   * @return string
   */
  public function getCurrentCssUrl();
}
