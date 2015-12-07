<?php
namespace Cms\Request\Media;

use Cms\Request\Base;

/**
 * Request object for Media GetByFilter
 *
 * @package      Cms
 * @subpackage   Request
 */
class GetByFilter extends Base
{
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $albumId;
  /**
   * @var integer
   */
  private $maxiconwidth;
  /**
   * @var integer
   */
  private $maxiconheight;
  /**
   * @var integer
   */
  private $limit;
  /**
   * @var integer
   */
  private $start = 0;
  /**
   * @var string
   */
  private $sort;
  /**
   * @var string
   */
  private $direction = null;
  /**
   * @var string
   */
  private $search = null;

  protected function setValues()
  {
    $this->setMaxIconwidth($this->getRequestParam('maxiconwidth'));
    $this->setMaxIconheight($this->getRequestParam('maxiconheight'));
    $this->setLimit($this->getRequestParam('limit'));
    $this->setAlbumId($this->getRequestParam('albumid'));
    $this->setStart($this->getRequestParam('start'));
    $this->setSort($this->getRequestParam('sort'));
    $this->setDirection($this->getRequestParam('direction'));
    $this->setWebsiteId($this->getRequestParam('websiteid'));
    $this->setSearch($this->getRequestParam('search'));
  }
  /**
   * @param integer $width
   */
  public function setMaxIconwidth($width)
  {
    $this->maxiconwidth = $width;
  }
  /**
   * @return integer
   */
  public function getMaxIconwidth()
  {
    return $this->maxiconwidth;
  }
  /**
   * @param integer $height
   */
  public function setMaxIconheight($height)
  {
    $this->maxiconheight = $height;
  }
  /**
   * @return integer
   */
  public function getMaxIconheight()
  {
    return $this->maxiconheight;
  }
  /**
   * @param integer $limit
   */
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  /**
   * @return integer
   */
  public function getLimit()
  {
    return $this->limit;
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
   * @param string $id
   */
  public function setAlbumId($id)
  {
    $this->albumId = $id;
  }
  /**
   * @return string
   */
  public function getAlbumId()
  {
    return $this->albumId;
  }
  /**
   * @param integer $start
   */
  public function setStart($start)
  {
    if ($start !== null) {
      $this->start = $start;
    }
  }
  /**
   * @return integer
   */
  public function getStart()
  {
    return $this->start;
  }
  /**
   * @param string $column Feld nach dem sortiert werden soll
   */
  public function setSort($column)
  {
    $this->sort = $column;
  }
  /**
   * @return string
   */
  public function getSort()
  {
    return $this->sort;
  }
  /**
   * @param string $sortDirection
   */
  public function setDirection($sortDirection)
  {
    if ($sortDirection !== null) {
      $this->direction = strtoupper($sortDirection);
    }
  }
  /**
   * @return string
   */
  public function getDirection()
  {
    return $this->direction;
  }
  /**
   * @param string $search
   */
  public function setSearch($search)
  {
    $this->search = $search;
  }
  /**
   * @return string
   */
  public function getSearch()
  {
    return $this->search;
  }
}
