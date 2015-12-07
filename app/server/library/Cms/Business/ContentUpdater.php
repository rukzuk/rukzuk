<?php


namespace Cms\Business;

use \Cms\Business\Website as WebsiteBusiness;
use \Cms\Business\Template as TemplateBusiness;
use \Cms\Business\TemplateSnippet as TemplateSnippetBusiness;
use \Cms\Business\Reparse as ReparseBusiness;
use Seitenbau\Registry;
use Seitenbau\Log as SbLog;

/**
 * @package Cms\Business
 *
 * @method \Cms\Service\ContentUpdater getService
 */
class ContentUpdater extends Base\Service
{
  /**
   * updates all unit formValues of all websites, templatesnippets and template and reparse all
   * linked pages
   */
  public function updateAllContents()
  {
    /** @var \Cms\Data\Website[] $allWebsites */
    $allWebsites = $this->getWebsiteBusiness()->getAll();
    foreach ($allWebsites as $website) {
      $this->updateAllContentsOfWebsite($website->getId());
    }
  }

  /**
   * @param string $websiteId
   */
  public function updateAllContentsOfWebsite($websiteId)
  {
    /** @var \Cms\Data\TemplateSnippet[] $allTemplateSnippets */
    $allTemplateSnippets = $this->getTemplateSnippetBusiness()->getAll($websiteId);
    foreach ($allTemplateSnippets as $templateSnippet) {
      if ($templateSnippet->isReadonly()) {
        continue;
      }
      try {
        $this->updateTemplateSnippetContent($websiteId, $templateSnippet->getId());
      } catch (\Exception $logOnly) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
      }
    }

    /** @var string[] $allTemplateIds */
    $allTemplateIds = $this->getTemplateBusiness()->getIdsByWebsiteId($websiteId);
    foreach ($allTemplateIds as $templateId) {
      try {
        $this->updateTemplateContent($websiteId, $templateId);
      } catch (\Exception $logOnly) {
        Registry::getLogger()->logException(__METHOD__, __LINE__, $logOnly, SbLog::ERR);
      }
    }
  }

  /**
   * @param string $websiteId
   * @param string $templateId
   */
  protected function updateTemplateContent($websiteId, $templateId)
  {
    $template = $this->getService()->updateDefaultFormValuesOfTemplate($websiteId, $templateId);
    $this->getReparseBusiness()->reparseTemplateLinkedPages($template, $websiteId);
  }

  /**
   * @param string $websiteId
   * @param string $templateId
   */
  protected function updateTemplateSnippetContent($websiteId, $templateId)
  {
    $this->getService()->updateDefaultFormValuesOfTemplateSnippet($websiteId, $templateId);
  }

  /**
   * @return WebsiteBusiness
   */
  protected function getWebsiteBusiness()
  {
    return $this->getBusiness('Website');
  }

  /**
   * @return TemplateBusiness
   */
  protected function getTemplateBusiness()
  {
    return $this->getBusiness('Template');
  }

  /**
   * @return TemplateSnippetBusiness
   */
  protected function getTemplateSnippetBusiness()
  {
    return $this->getBusiness('TemplateSnippet');
  }

  /**
   * @return ReparseBusiness
   */
  protected function getReparseBusiness()
  {
    return $this->getBusiness('Reparse');
  }

  /**
   * @param array  $identity
   * @param string $rightname
   * @param mixed  $check
   *
   * @return bool
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // superuser has all rights
    if ($this->isSuperuser($identity)) {
      return true;
    }

    return false;
  }
}
