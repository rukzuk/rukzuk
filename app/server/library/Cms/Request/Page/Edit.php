<?php


namespace Cms\Request\Page;

use Cms\Request\Base;

/**
 * @package      Cms\Request
 * @subpackage   Page
 *
 * @SWG\Model(id="Request/Page/Edit")
 */
class Edit extends Base
{
  /**
   * @var string
   * @SWG\Property(required=true)
   */
  private $runId;

  /**
   * @var string
   * @SWG\Property(required=true, description="ID of the page")
   */
  private $id;

  /**
   * @var string
   * @SWG\Property(required=true, description="ID of the associated website")
   */
  private $websiteId;

  /**
   * @var string
   * @SWG\Property(required=false, description="new page content")
   */
  private $content;


  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setContent($this->getRequestParam('content'));
  }

  public function setRunId($runId)
  {
    $this->runId = $runId;
  }

  public function getRunId()
  {
    return $this->runId;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function getId()
  {
    return $this->id;
  }

  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function setContent($content)
  {
    $this->content = $content;
  }

  /**
   * @return boolean
   */
  public function isIdSet()
  {
    return $this->id !== null && $this->id !== 0 && $this->id !== '';
  }
}
