<?php


namespace Cms\Service;

use Cms\Service\Base\Plain as PlainServiceBase;
use Cms\ContentUpdater\DefaultFormValuesUpdater;
use Cms\Service\Modul as ModuleService;
use Cms\Service\Template as TemplateService;
use Cms\Service\TemplateSnippet as TemplateSnippetService;

/**
 * @package Cms\Service
 */
class ContentUpdater extends PlainServiceBase
{
  /**
   * @var ModuleService
   */
  private $moduleService;

  /**
   * @var TemplateService
   */
  private $templateService;

  /**
   * @var TemplateSnippetService
   */
  private $templateSnippetService;

  /**
   * @var DefaultFormValuesUpdater[]
   */
  private $defaultFormValuesUpdater = array();

  /**
   * @param string $websiteId
   * @param string $templateId
   *
   * @return \Cms\Data\Template|mixed
   */
  public function updateDefaultFormValuesOfTemplate($websiteId, $templateId)
  {
    $templateService = $this->getTemplateService();
    $template = $templateService->getById($templateId, $websiteId);
    $content = json_decode($template->getContent());
    if (!is_array($content)) {
      return $template;
    }
    $this->updateDefaultFormValuesInContent($websiteId, $content);
    return $templateService->update($templateId, $websiteId, array(
      'content' => $content,
    ));
  }

  /**
   * @param string $websiteId
   * @param string $templateId
   *
   * @return \Cms\Data\TemplateSnippet
   */
  public function updateDefaultFormValuesOfTemplateSnippet($websiteId, $templateId)
  {
    $templateSnippetService = $this->getTemplateSnippetService();
    $snippet = $templateSnippetService->getById($websiteId, $templateId);
    $content = json_decode($snippet->getContent());
    if (!is_array($content)) {
      return $snippet;
    }
    $this->updateDefaultFormValuesInContent($websiteId, $content);
    return $templateSnippetService->update($websiteId, $templateId, array(
      'content' => $content,
    ));
  }

  /**
   * @param string $websiteId
   * @param array  $content
   */
  protected function updateDefaultFormValuesInContent($websiteId, &$content)
  {
    $this->getDefaultFormValuesUpdater($websiteId)->updateDefaultFormValues($content);
  }

  /**
   * @param string $websiteId
   *
   * @return DefaultFormValuesUpdater
   */
  protected function getDefaultFormValuesUpdater($websiteId)
  {
    if (!isset($this->defaultFormValuesUpdater[$websiteId])) {
      $this->defaultFormValuesUpdater[$websiteId] = new DefaultFormValuesUpdater(
          $websiteId,
          $this->getModuleService()
      );
    }
    return $this->defaultFormValuesUpdater[$websiteId];
  }

  /**
   * @return TemplateService
   */
  protected function getTemplateService()
  {
    if (!isset($this->templateService)) {
      $this->templateService = $this->getService('Template');
    }
    return $this->templateService;
  }

  /**
   * @return TemplateSnippetService
   */
  protected function getTemplateSnippetService()
  {
    if (!isset($this->templateSnippetService)) {
      $this->templateSnippetService = $this->getService('TemplateSnippet');
    }
    return $this->templateSnippetService;
  }

  /**
   * @return ModuleService
   */
  protected function getModuleService()
  {
    if (!isset($this->moduleService)) {
      $this->moduleService = $this->getService('Modul');
    }
    return $this->moduleService;
  }
}
