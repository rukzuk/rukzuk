<?php
namespace Cms\Request\Template;

use Cms\Request\Base;

/**
 * Request object for Template-Meta edit
 *
 * @package      Cms
 * @subpackage   Request\Template
 */

class EditMeta extends Base
{
  /**
   * @var string
   */
  private $runId;

  /**
   * @var string
   */
  private $id = null;

  /**
   * @var string
   */
  private $websiteId = null;

  /**
   * @var string
   */
  private $name;

  protected function setValues()
  {
    $this->setRunId($this->getRequestParam('runid'));
    $this->setId($this->getRequestParam('id'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setName($this->getRequestParam('name'));
  }

  /**
   * @param string $runId
   */
  public function setRunId($runId)
  {
    $this->runId = $runId;
  }
  /**
   * @return string
   */
  public function getRunId()
  {
    return $this->runId;
  }

  /**
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    if ($this->id === "" || $this->id === 0) {
      return null;
    }
    return $this->id;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = trim($name);
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}
