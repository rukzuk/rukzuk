<?php


namespace Cms\Request\Creator;

use Cms\Request\Base;

class Prepare extends Base
{
  /**
   * @var string
   */
  private $creatorName;

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var string
   */
  private $prepare;

  /**
   * @var array
   */
  private $info;

  /**
   * set the request object values
   */
  protected function setValues()
  {
    $this->setCreatorName($this->getRequestParam('creatorname'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setPrepare($this->getRequestParam('prepare'));
    $this->setInfo($this->getRequestParam('info'));
  }

  /**
   * @return string
   */
  public function getCreatorName()
  {
    return $this->creatorName;
  }

  /**
   * @param string $creatorName
   */
  protected function setCreatorName($creatorName)
  {
    $this->creatorName = $creatorName;
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $websiteId
   */
  protected function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  /**
   * @return string
   */
  public function getPrepare()
  {
    return $this->prepare;
  }

  /**
   * @param string $prepare
   */
  protected function setPrepare($prepare)
  {
    $this->prepare = $prepare;
  }

  /**
   * @return array
   */
  public function getInfo()
  {
    return $this->info;
  }

  /**
   * @param array $info
   */
  protected function setInfo($info)
  {
    if ($info instanceof \stdClass) {
      $this->info = json_decode(json_encode($info), true);
    } else {
      $this->info = $info;
    }
  }
}
