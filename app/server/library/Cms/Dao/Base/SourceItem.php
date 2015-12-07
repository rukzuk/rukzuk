<?php


namespace Cms\Dao\Base;

/**
 * @package Cms\Dao
 */
class SourceItem extends AbstractSourceItem
{
  /**
   * @var string
   */
  private $id;

  /**
   * @var bool
   */
  private $overwritten = false;

  /**
   * @param string $id
   * @param string $directory
   * @param string $url
   * @param string $type
   * @param bool   $readonly
   * @param bool   $exportable
   */
  public function __construct($id, $directory, $url, $type, $readonly, $exportable)
  {
    parent::__construct($directory, $url, $type, $readonly, $exportable);
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return boolean
   */
  public function isOverwritten()
  {
    return $this->overwritten;
  }

  /**
   * @param boolean $overwritten
   */
  public function setOverwritten($overwritten)
  {
    $this->overwritten = $overwritten;
    $this->resetCacheKeyCache();
  }

  /**
   * @return array
   */
  public function toArray()
  {
    $toArray = parent::toArray();
    $toArray['overwritten'] = $this->isOverwritten();
    return $toArray;
  }
}
