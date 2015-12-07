<?php
namespace Cms\Business;

use Cms\Reparser;
use Cms\Data;

/**
 * Stellt die Business-Logik fuer Reparse zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Reparse extends Base\Service
{
  protected $pageBusiness = null;
  protected $templateBusiness = null;
  
  /**
   * Content einer Page anhand eines Templates durch Reparsen erzeugen
   *
   * @param Cms\Data\Template $template
   */
  public function generateNewPageContent(\Cms\Data\Template $template)
  {
    try {
      $newPageContent = Reparser::reparseContent(
          $template->getWebsiteid(),
          '',
          '',
          $template->getContent(),
          Reparser::TYPE_NEW
      );
    } catch (\Exception $e) {
      $newPageContent = array();
      $data = (method_exists($e, 'getData'))
            ? $e->getData()
            : array();
      \Cms\ExceptionStack::addException(
          new \Cms\Exception(803, __METHOD__, __LINE__, $data, $e)
      );
    }
    
    return $newPageContent;
  }

  /**
   * Reparse einer Website
   *
   * @param string $websiteId
   *
   * @return array
   */
  public function reparseWebsite($websiteId)
  {
    $pageIds = $this->getPageBusiness()->getIdsByWebsiteId($websiteId);
    return $this->reparsePages($websiteId, $pageIds);
  }

  /**
   * Reparsing the pages related to the given website id and template
   *
   * @param Cms\Data\Template $template
   * @param string $websiteId
   * @return array  ids of reparsed pages
   */
  public function reparseTemplateLinkedPages(\Cms\Data\Template $template, $websiteId)
  {
    $reparsedPageIds = array();
    $pageBusiness = $this->getPageBusiness();
    $pageIds = $pageBusiness->getIdsByWebsiteIdAndTemplateId($websiteId, $template->getId());
    if (is_array($pageIds)) {
      foreach ($pageIds as $pageId) {
        try {
          $page = $pageBusiness->getById($pageId, $websiteId);
          if ($this->reparsePage($page, $template)) {
            $reparsedPageIds[] = $pageId;
          }
        } catch (\Exception $e) {
          \Cms\ExceptionStack::addException($e);
        }
      }
    }
    
    if (\Cms\ExceptionStack::hasErrors()) {
      \Cms\ExceptionStack::throwErrors();
    }
    
    return $reparsedPageIds;
  }

  /**
   * Reparsen einer Page
   *
   * @param Cms\Data\Page $page
   */
  public function reparsePage(\Cms\Data\Page $page)
  {
    $template = $this->getTemplateBusiness()
                     ->getById($page->getTemplateId(), $page->getWebsiteId());
    return $this->doReparsePage($page, $template);
  }

  /**
   * Reparse von Pages
   *
   * @param       $websiteId
   * @param array $pageIds
   *
   * @return array
   */
  private function reparsePages($websiteId, array $pageIds)
  {
    if (count($pageIds) == 0) {
      return array();
    }

    $idsOfReparsedPages = array();
    $pageBusiness = $this->getPageBusiness();
    foreach ($pageIds as $pageId) {
      try {
        $page = $pageBusiness->getById($pageId, $websiteId);
        if ($this->reparsePage($page)) {
          $idsOfReparsedPages[] = $page->getId();
        }
      } catch (\Exception $e) {
        \Cms\ExceptionStack::addException($e);
      }
    }

    if (\Cms\ExceptionStack::hasErrors()) {
      \Cms\ExceptionStack::throwErrors();
    }

    return $idsOfReparsedPages;
  }

  /**
   * reparsing the page unsing specific template
   *
   * @param \Cms\Data\Page      $page
   * @param \Cms\Data\Template  $template
   * @return boolean
   */
  private function doReparsePage(\Cms\Data\Page $page, \Cms\Data\Template $template)
  {
    if ($page->getTemplatecontentchecksum() != $template->getContentchecksum()) {
      try {
        if (Reparser::reparseAndUpdatePage($page, $template)) {
          return true;
        }
      } catch (\Exception $e) {
        $data = (method_exists($e, 'getData'))
              ? $e->getData()
              : array();
        $data['pageId'] = $page->getId();
        $data['pageName'] = $page->getName();
        throw new \Cms\Exception(801, __METHOD__, __LINE__, $data, $e);
      }
    }
    return false;
  }
  
  protected function getPageBusiness()
  {
    if (isset($this->pageBusiness)) {
      return $this->pageBusiness;
    }
    $this->pageBusiness = $this->getBusiness('Page');
    return $this->pageBusiness;
  }
  
  protected function getTemplateBusiness()
  {
    if (isset($this->templateBusiness)) {
      return $this->templateBusiness;
    }
    $this->templateBusiness = $this->getBusiness('Template');
    return $this->templateBusiness;
  }
}
