<?php
namespace Cms\Render\PageUrlHelper;

use Render\PageUrlHelper\AbstractPageUrlHelper;
use Render\RenderContext;

/**
 * Class CmsPageUrlHelper
 * Generate URLs *inside* of the CMS (for Edit and Preview Modes)
 *
 * @package Render\PageUrlHelper
 */
class CmsPageUrlHelper extends AbstractPageUrlHelper
{

  /**
   * @var string
   */
  private $currentPageOrTemplateId;
  /**
   * @var string
   */
  private $isTemplate;

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var string
   */
  private $renderPageServiceUrl;

  /**
   * @var string
   */
  private $renderPageCssServiceUrl;

  /**
   * @var string
   */
  private $renderTemplateServiceUrl;

  /**
   * @var string
   */
  private $renderTemplateCssServiceUrl;


  /**
   * @var string
   */
  private $renderMode;

  /**
   * @param string      $websiteId
   * @param string      $currentPageOrTemplateId
   * @param bool        $isTemplate
   * @param string      $renderPageServiceUrl
   * @param string      $renderPageCssServiceUrl
   * @param string      $renderTemplateServiceUrl
   * @param string      $renderTemplateCssServiceUrl
   * @param string      $renderMode
   * @param string|null $baseUrl
   */
  public function __construct(
      $websiteId,
      $currentPageOrTemplateId,
      $isTemplate,
      $renderPageServiceUrl,
      $renderPageCssServiceUrl,
      $renderTemplateServiceUrl,
      $renderTemplateCssServiceUrl,
      $renderMode = RenderContext::RENDER_MODE_PREVIEW,
      $baseUrl = null
  ) {
    parent::__construct($baseUrl);
    $this->websiteId = $websiteId;
    $this->currentPageOrTemplateId = $currentPageOrTemplateId;
    $this->isTemplate = $isTemplate;
    $this->renderPageServiceUrl = $renderPageServiceUrl;
    $this->renderPageCssServiceUrl = $renderPageCssServiceUrl;
    $this->renderTemplateServiceUrl = $renderTemplateServiceUrl;
    $this->renderTemplateCssServiceUrl = $renderTemplateCssServiceUrl;
    $this->renderMode = $renderMode;
  }

  /**
   * Url of a specific Page
   *
   * @param string $pageId
   * @param array  $parameters
   * @param bool   $absoluteUrl
   *
   * @return string
   */
  public function getPageUrl($pageId, array $parameters, $absoluteUrl)
  {
    $renderParams = array(
      'pageid' => $pageId,
      'websiteid' => $this->websiteId,
    );

    if ($this->renderMode) {
      $renderParams['mode'] = $this->renderMode;
    }

    $url = $this->renderPageServiceUrl . urlencode(\json_encode($renderParams));
    return $this->createUrlWithQuery($url, $parameters, $absoluteUrl);
  }

  /**
   * Url of the currently rendered Page (or Template)
   *
   * @return string
   */
  public function getCurrentUrl()
  {
    if ($this->isTemplate) {
      $renderParams = array(
        'templateid' => $this->currentPageOrTemplateId,
        'websiteid' => $this->websiteId,
      );

      if ($this->renderMode) {
        $renderParams['mode'] = $this->renderMode;
      }

      return $this->renderTemplateServiceUrl . urlencode(\json_encode($renderParams));
    } else {
      return $this->getPageUrl($this->currentPageOrTemplateId, array(), false);
    }
  }

  /**
   * Url of the corresponding CSS file to this Page (or Template)
   *
   * @return string
   */
  public function getCurrentCssUrl()
  {
    if ($this->isTemplate) {
      $renderParams = array(
        'templateid' => $this->currentPageOrTemplateId,
        'websiteid' => $this->websiteId,
      );

      if ($this->renderMode) {
        $renderParams['mode'] = $this->renderMode;
      }

      return $this->renderTemplateCssServiceUrl . urlencode(\json_encode($renderParams));
    } else {
      $renderParams = array(
        'pageid' => $this->currentPageOrTemplateId,
        'websiteid' => $this->websiteId,
      );

      if ($this->renderMode) {
        $renderParams['mode'] = $this->renderMode;
      }

      return $this->renderPageCssServiceUrl . urlencode(\json_encode($renderParams));
    }
  }
}
