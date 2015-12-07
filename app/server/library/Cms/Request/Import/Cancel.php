<?php
namespace Cms\Request\Import;

use Cms\Request\Base;

/**
 * Request object for Import Cancel
 *
 * @package      Cms
 * @subpackage   Request
 */
class Cancel extends Base
{
  /**
   * @var importId
   */
  private $importId;
  /**
   * @param string $id
   */
  public function setImportId($id)
  {
    $this->importId = $id;
  }
  /**
   * @return string
   */
  public function getImportId()
  {
    return $this->importId;
  }
  
  protected function setValues()
  {
    $this->setImportId($this->getRequestParam('importid'));
  }
}
