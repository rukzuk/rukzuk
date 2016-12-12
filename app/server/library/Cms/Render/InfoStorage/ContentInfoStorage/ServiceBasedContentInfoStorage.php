<?php


namespace Cms\Render\InfoStorage\ContentInfoStorage;

use \Cms\Service\Template as TemplateService;
use Render\InfoStorage\ContentInfoStorage\IContentInfoStorage;
use Render\InfoStorage\ContentInfoStorage\Exceptions\TemplateDoesNotExists;

class ServiceBasedContentInfoStorage implements IContentInfoStorage
{
  private $templateService;
  private $websiteId;

  /**
   * @param string          $websiteId
   * @param TemplateService $templateService
   */
  public function __construct($websiteId, TemplateService $templateService)
  {
    $this->websiteId = $websiteId;
    $this->templateService = $templateService;
  }

  /**
   * @param  $templateId
   *
   * @return array
   */
  public function getTemplateContent($templateId)
  {
    $template = $this->getTemplateById($templateId);
    $content = json_decode($template->getContent(), true);
    if (isset($content[0]) && is_array($content[0])) {
      return $content[0];
    } else {
      return array();
    }
  }

  /**
   * @return TemplateService
   */
  protected function getTemplateService()
  {
    return $this->templateService;
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $template
   *
   * @return \Cms\Data\Template
   * @throws TemplateDoesNotExists
   */
  protected function getTemplateById($template)
  {
    try {
      return $this->getTemplateService()->getById($template, $this->getWebsiteId());
    } catch (\Exception $e) {
      throw new TemplateDoesNotExists();
    }
  }
}
