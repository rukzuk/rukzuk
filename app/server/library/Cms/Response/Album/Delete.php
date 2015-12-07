<?php
namespace Cms\Response\Album;

/**
 * Response Ergebnis fuer Album Delete
 *
 * @package      Cms
 * @subpackage   Response
 */
class Delete
{
  /**
   * @var array
   */
  public $nonDeletables = array();
  /**
   * @param array $nonDeletables
   */
  public function __construct(array $nonDeletables)
  {
    $this->nonDeletables = $nonDeletables;
  }
  /**
   * @return array
   */
  public function getNonDeletables()
  {
    return $this->nonDeletables;
  }
}
