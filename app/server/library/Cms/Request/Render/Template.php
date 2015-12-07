<?php
namespace Cms\Request\Render;

use Cms\Request\Base;

/**
 * Request object for Render Template
 *
 * @package      Cms
 * @subpackage   Request
 */

class Template extends Base
{
  private $templateId;

  private $websiteId;

  private $data;

  private $mode;

  protected function setValues()
  {
    $this->setTemplateId($this->getRequestParam('templateid'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setData($this->getRequestParam('data'));
    $this->setMode($this->getRequestParam('mode'));
  }

  public function getTemplateId()
  {
    return $this->templateId;
  }

  public function setTemplateId($templateId)
  {
    $this->templateId = $templateId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getData()
  {
    return $this->data;
  }

  public function setData($data)
  {
    $this->data = $data;
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function setMode($mode)
  {
    $this->mode = strtoupper($mode);
  }
}
