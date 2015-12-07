<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Lock as Request;
use Orm\Data;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_NotEmpty as NotEmptyValidator;
use Cms\Business\Lock as LockBusiness;

/**
 * Lock request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */

class Lock extends Base
{
  private $expectedTypes = array(
      LockBusiness::LOCK_TYPE_PAGE,
      LockBusiness::LOCK_TYPE_TEMPLATE,
      LockBusiness::LOCK_TYPE_MODULE,
      LockBusiness::LOCK_TYPE_WEBSITE);

  /**
   * @param Cms\Request\Lock\GetAll $actionRequest
   */
  protected function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param Cms\Request\Lock\Lock $actionRequest
   */
  protected function validateMethodLock(Request\Lock $actionRequest)
  {
    $this->validateType($actionRequest->getType());
    $this->validateRunId($actionRequest->getRunId());
    $this->validateIdByType($actionRequest->getId(), $actionRequest->getType());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateIsBoolean($actionRequest->getOverride(), 'override');
  }

  /**
   * @param Cms\Request\Lock\Unlock $actionRequest
   */
  protected function validateMethodUnlock(Request\Unlock $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateItems($actionRequest->getItems());
  }

  /**
   * @param string $id
   * @param string $type
   * @return boolean
   */
  private function validateIdByType($id, $type)
  {
    switch ($type)
    {
      case LockBusiness::LOCK_TYPE_PAGE;
        $idValidator = new UniqueIdValidator(
            Data\Page::ID_PREFIX,
            Data\Page::ID_SUFFIX
        );
            break;
      case LockBusiness::LOCK_TYPE_TEMPLATE:
        $idValidator = new UniqueIdValidator(
            Data\Template::ID_PREFIX,
            Data\Template::ID_SUFFIX
        );
            break;
      case LockBusiness::LOCK_TYPE_MODULE:
        $idValidator = new ModuleIdValidator(true);
            break;
      case LockBusiness::LOCK_TYPE_WEBSITE:
        // id darf nicht angegeben sein
        if (isset($id) && !empty($id)) {
          $this->addError(new Error('id', $id, array('Bei type="website" darf keine id angegeben werden')));
        }
            return true;
        break;
      default:
        $idValidator = false;
    }
    
    if ($idValidator !== false) {
      if (!$idValidator->isValid($id)) {
        $messages = array_values($idValidator->getMessages());
        $this->addError(new Error('id', $id, $messages));
        return false;
      }
      return true;
    }
    
    $messages = array('ID konnte keinem Typ zugeordnet werden');
    $this->addError(new Error('id', $id, $messages));
    return false;
  }

  /**
   * @param string $type
   * @return boolean
   */
  private function validateType($type)
  {
    $typeValidator = new \Zend_Validate_InArray($this->expectedTypes);
    $typeValidator->setMessage(
        "'%value%' ist kein unterstuetzter Typ",
        \Zend_Validate_InArray::NOT_IN_ARRAY
    );

    if (!$typeValidator->isValid($type)) {
      $messages = array_values($typeValidator->getMessages());
      $this->addError(new Error('type', $type, $messages));

      return false;
    }
    return true;
  }

  /**
   * @param  boolean $value
   * @param  string  $field
   * @return boolean
   */
  private function validateIsBoolean($value, $field)
  {
    $booleanValidator = new BooleanValidator();

    if (!$booleanValidator->isValid($value)) {
      $messages = array_values($booleanValidator->getMessages());
      $this->addError(new Error($field, $value, $messages));
      return false;
    }

    return true;
  }

  /**
   * @param string $websiteId
   * @return boolean
   */
  private function validateItems($items)
  {
    // Alle Items durchlaufen und pruefen
    if (isset($items) && is_array($items)) {
      foreach ($items as $nextItem) {
        // Items muessen ein Array sein
        if (!is_object($nextItem)) {
          $messages = array('Falsches "items"-Format!');
          $this->addError(new Error('items', $items, $messages));
          return false;
        }

        // Einzelne Werte pruefen
        $this->validateType(
            (isset($nextItem->type) ? $nextItem->type : null)
        );
        $this->validateIdByType(
            (isset($nextItem->id) ? $nextItem->id : null),
            (isset($nextItem->type) ? $nextItem->type : null)
        );
        $this->validateWebsiteId(
            (isset($nextItem->websiteId) ? $nextItem->websiteId : null)
        );
      }
    } // Items muss ein Array sein
    elseif (isset($items)) {
      $messages = array('Falsches "items"-Format!');
      $this->addError(new Error('items', $items, $messages));
      return false;
    }

    return true;
  }
}
