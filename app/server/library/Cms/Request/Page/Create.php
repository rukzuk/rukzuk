<?php
namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * Request object fuer Page create
 *
 * @package      Cms
 * @subpackage   Request
 */

class Create extends Base
{
  protected $parentId;

  protected $insertBeforeId;

  protected $websiteId;

  protected $templateId;

  protected $mediaId;

  protected $name;

  protected $description;

  protected $date;

  protected $inNavigation;

  protected $navigationTitle;

  protected $content;

  protected $pageType;

  protected $pageAttributes;

  protected function setValues()
  {
    $this->setParentId(($this->getRequestParam('parentid')));
    $this->setInsertBeforeId($this->getRequestParam('insertbeforeid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setTemplateId($this->getRequestParam('templateid'));
    $this->setMediaId($this->getRequestParam('mediaid'));
    $this->setName($this->getRequestParam('name'));
    $this->setDescription($this->getRequestParam('description'));
    $this->setDate($this->getRequestParam('date'));
    $this->setInNavigation($this->getRequestParam('innavigation'));
    $this->setNavigationTitle($this->getRequestParam('navigationtitle'));
    $this->setContent($this->getRequestParam('content'));
    $this->setPageType($this->getRequestParam('pagetype'));
    $this->setPageAttributes($this->getRequestParam('pageattributes'));
  }

  public function getParentId()
  {
    return $this->parentId;
  }

  public function setParentId($parentId)
  {
    $this->parentId = $parentId;
  }

  public function getInsertBeforeId()
  {
    return $this->insertBeforeId;
  }

  public function setInsertBeforeId($insertBeforeId)
  {
    $this->insertBeforeId = (string) $insertBeforeId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getTemplateId()
  {
    return $this->templateId;
  }

  public function setTemplateId($templateId)
  {
    $this->templateId = $templateId;
  }

  public function getMediaId()
  {
    return $this->mediaId;
  }

  public function setMediaId($mediaId)
  {
    $this->mediaId = $mediaId;
  }

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getDescription()
  {
    return $this->description;
  }

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getDate()
  {
    return $this->date;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function getInNavigation()
  {
    return $this->inNavigation;
  }

  public function setInNavigation($inNavigation)
  {
    $this->inNavigation = $inNavigation;
  }

  public function getNavigationTitle()
  {
    return $this->navigationTitle;
  }

  public function setNavigationTitle($navigationTitle)
  {
    $this->navigationTitle = $navigationTitle;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function setContent($content)
  {
    $this->content = $content;
  }
  public function getPageType()
  {
    return $this->pageType;
  }

  public function setPageType($pageType)
  {
    $this->pageType = $pageType;
  }

  public function getPageAttributes()
  {
    return $this->pageAttributes;
  }

  public function setPageAttributes($pageAttributes)
  {
    if (is_object($pageAttributes) || is_array($pageAttributes)) {
      $this->pageAttributes = json_encode($pageAttributes);
    } else {
      $this->pageAttributes = $pageAttributes;
    }
  }
}
