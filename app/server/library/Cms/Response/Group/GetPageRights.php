<?php
namespace Cms\Response\Group;

use Cms\Response;

/**
 * @package      Cms
 * @subpackage   Response
 */
class GetPageRights implements Response\IsResponseData
{
  /**
   * @var boolean
   */
  public $allRights;
  
  /**
   * @var array
   */
  public $navigation;
  
  /**
   * @param array   $data
   * @param boolean $allRights
   */
  public function __construct(array $data = array(), $allRights = false)
  {
    $this->navigation = $data;
    $this->allRights  = $allRights;
  }
}
