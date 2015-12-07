<?php
namespace Cms\Request\Validator;

use Cms\Request\Modul as Request;
use Cms\Validator\ModuleId as ModuleIdValidator;

/**
 * Modul request validator
 *
 * @package      Application
 * @subpackage   Controller
 */
class Modul extends Base
{
  /**
   * @param \Cms\Request\Modul\GetAll $actionRequest
   */
  protected function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Modul\Lock $actionRequest
   */
  protected function validateMethodLock(Request\Lock $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateModulId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBoolean($actionRequest->getOverride(), 'override');
  }

  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateModulId($id)
  {
    $modulIdValidator = new ModuleIdValidator(true);
    if (!$modulIdValidator->isValid($id)) {
      $messages = array_values($modulIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }
}
