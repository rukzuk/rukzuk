<?php
namespace Cms\Response\Builder;

use Cms\Response\IsResponseData;

/**
 * GetByWebsiteId
 *
 * @package      Cms
 * @subpackage   Response
 */
class GetByWebsiteId implements IsResponseData
{
  /**
   * @var array
   */
  public $builds = array();

  /**
   * @param array $builds
   */
  public function __construct($builds = array())
  {
    $this->setBuilds($builds);
  }
  /**
   * @return array
   */
  public function getBuilds()
  {
    return $this->builds;
  }
  /**
   * @param array $builds
   */
  protected function setBuilds(array $builds)
  {
    if (count($builds) > 0) {
      foreach ($builds as $build) {
        $this->builds[] = new \Cms\Response\Build($build);
      }
    }
  }
}
