<?php
namespace Cms\Response\Modul;

use Cms\Response\IsResponseData;

/**
 * Response Ergebnis fuer GetAll
 *
 * @package      Cms
 * @subpackage   Response
 *
 * @SWG\Model(id="Response/Module/GetAll")
 */
class GetAll implements IsResponseData
{
  /**
   * @var array
   *
   * @SWG\Property(
   *      name="modules",
   *      type="array",
   *      items="$ref:Module",
   *      required=true,
   *      description="list of modules")
   */
  public $modules = array();

  /**
   * @param array $modules
   */
  public function __construct(array $modules = array())
  {
    $this->setModules($modules);
  }

  /**
   * @return array
   */
  public function getModules()
  {
    return $this->modules;
  }
  
  /**
   * @param array $modules
   */
  protected function setModules(array $modules)
  {
    foreach ($modules as $modul) {
      $this->modules[] = new \Cms\Response\Modul($modul);
    }
  }
}
