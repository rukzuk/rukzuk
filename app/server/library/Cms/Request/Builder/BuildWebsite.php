<?php
namespace Cms\Request\Builder;

use Cms\Request\Base;

/**
 * BuildWebsite
 *
 * @package      Cms
 * @subpackage   Request
 */
class BuildWebsite extends Base
{
  /**
   * @var string
   */
  private $websiteId = null;
  
  /**
   * @var string
   */
  private $comment = null;

  protected function setValues()
  {
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setComment($this->getRequestParam('comment'));
  }
  /**
   * @param string $id
   */
  public function setWebsiteId($id)
  {
    $this->websiteId = $id;
  }
  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
  /**
   * @param string $comment
   */
  public function setComment($comment)
  {
    $this->comment = $comment;
  }
  /**
   * @return string
   */
  public function getComment()
  {
    return $this->comment;
  }
}
