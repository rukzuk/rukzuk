<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Group as Request;
use Orm\Data\Group as GroupData;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\UserId as UserIdValidator;
use Cms\Validator\IsArray as IsArrayValidator;
use Cms\Validator\UserRight as UserRightValidator;
use Cms\Validator\PageRights as PageRightsValidator;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use Cms\Request\Validator\Error;

/**
 * Group request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Group extends Base
{
  /**
   * @param \Cms\Request\Group\SetPageRights $actionRequest
   */
  public function validateMethodSetPageRights(Request\SetPageRights $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateIsBoolean($actionRequest->getAllRights(), 'allrights');
    $this->validatePageRights($actionRequest->getRights());
  }
  
  /**
   * @param \Cms\Request\Group\AddUsers $actionRequest
   */
  public function validateMethodAddUsers(Request\AddUsers $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($this->validateIdsComeInAnArray($actionRequest->getUserIds())) {
      foreach ($actionRequest->getUserIds() as $userId) {
        $this->validateUserId($userId);
      }
    }
  }

  /**
   * @param \Cms\Request\Group\RemoveUsers $actionRequest
   */
  public function validateMethodRemoveUsers(Request\RemoveUsers $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($this->validateIdsComeInAnArray($actionRequest->getUserIds())) {
      foreach ($actionRequest->getUserIds() as $userId) {
        $this->validateUserId($userId);
      }
    }
  }

  /**
   * @param \Cms\Request\Group\Create $actionRequest
   */
  public function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateName($actionRequest->getName());

    if ($actionRequest->getRights() !== null) {
      if ($this->validateRightsComeInAnArray($actionRequest->getRights())) {
        foreach ($actionRequest->getRights() as $right) {
          $this->validateRight($right);
        }
      }
    }
  }
  /**
   * @param \Cms\Request\Group\Copy $actionRequest
   */
  public function validateMethodCopy(Request\Copy $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateName($actionRequest->getName());
  }
  

  /**
   * @param \Cms\Request\Group\Edit $actionRequest
   */
  public function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());

    if ($actionRequest->getName() !== null) {
      $this->validateName($actionRequest->getName());
    }

    if ($actionRequest->getRights() !== null
        && $actionRequest->getRights() !== array()) {
      if ($this->validateRightsComeInAnArray($actionRequest->getRights())) {
        $nonAllowedAreas = array('pages');
        foreach ($actionRequest->getRights() as $right) {
          $this->validateRight($right, $nonAllowedAreas);
        }
      }
    }
  }

  /**
   * @param \Cms\Request\Group\GetById $actionRequest
   */
  public function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateGroupId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Group\Delete $actionRequest
   */
  public function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateGroupId($actionRequest->getId());
  }

  /**
   * @param \Cms\Request\Group\GetAll $actionRequest
   */
  public function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Group\GetPageRights $actionRequest
   */
  public function validateMethodGetPageRights(Request\GetPageRights $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateGroupId($actionRequest->getId());
  }
  
  /**
   * @param  array $rights
   * @return boolean
   */
  private function validatePageRights($rights)
  {
    $rightsValidator = new PageRightsValidator();
    
    if (!$rightsValidator->isValid($rights)) {
      $messages = array_values($rightsValidator->getMessages());
      $this->addError(new Error('rights', json_encode($rights), $messages));

      return false;
    }
    
    return true;
  }
  
  /**
   * @param  string $right
   * @param  arrray $nonAllowedAreas
   * @return boolean
   */
  private function validateRight($right, array $nonAllowedAreas = array())
  {
    $rightValidator = new UserRightValidator($nonAllowedAreas);

    if (!$rightValidator->isValid($right)) {
      $messages = array_values($rightValidator->getMessages());
      $this->addError(new Error('right', json_encode($right), $messages));

      return false;
    }

    return true;
  }

  /**
   * @param mixed
   * @return boolean
   */
  private function validateRightsComeInAnArray($rights)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $isArrayValidator->setMessage(
        "Rights '%value%' sind kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );

    if (!$isArrayValidator->isValid($rights)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('rights', $rights, $messages));

      return false;
    }

    return true;
  }
  
  /**
   * @param mixed
   * @return boolean
   */
  private function validateIdsComeInAnArray($ids)
  {
    $isArrayValidator = new IsArrayValidator;
    $isArrayValidator->setMessage(
        "UserIds '%value%' sind kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    $isArrayValidator->setMessage(
        "Angegebene UserIds '%value%' sind ein leerer Array",
        IsArrayValidator::INVALID_EMPTY_ARRAY
    );

    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('userids', $ids, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param  string $name
   * @return boolean
   */
  private function validateName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 2,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Gruppen name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Gruppen name zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($name))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('name', $name, $messages));

      return false;
    }

    return true;
  }
  
  /**
   * @param string $id
   * @return boolean
   */
  private function validateUserId($id)
  {
    $userIdValidator = new UserIdValidator();

    if (!$userIdValidator->isValid($id)) {
      $messages = array_values($userIdValidator->getMessages());
      $this->addError(new Error('userid', $id, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param string $id
   * @return boolean
   */
  private function validateGroupId($id)
  {
    $groupIdValidator = new UniqueIdValidator(
        GroupData::ID_PREFIX,
        GroupData::ID_SUFFIX
    );

    if (!$groupIdValidator->isValid($id)) {
      $messages = array_values($groupIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));

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
}
