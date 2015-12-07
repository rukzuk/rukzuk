<?php


namespace Cms\Request\Validator;

use Cms\Request\Creator\Prepare as PrepareRequest;
use Cms\Validator\CreatorName as CreatorNameValidator;
use Zend_Validate_StringLength as StringLengthValidator;
use Cms\Validator\IsArray as IsArrayValidator;

class Creator extends Base
{
  /**
   * validate the prepare action request
   *
   * @param \Cms\Request\Creator\Prepare $actionRequest
   */
  protected function validateMethodPrepare(PrepareRequest $actionRequest)
  {
    $this->validateCreatorName($actionRequest->getCreatorName(), 'creatorname');
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'websiteid');
    $this->validatePrepare($actionRequest->getPrepare(), 'prepare');
    $this->validateInfo($actionRequest->getInfo(), 'info');
  }

  /**
   * @param mixed|string $creatorName
   * @param string       $requestKey
   *
   * @return bool
   */
  protected function validateCreatorName($creatorName, $requestKey)
  {
    $creatorNameValidator = new CreatorNameValidator();
    if (!$creatorNameValidator->isValid($creatorName)) {
      $messages = array_values($creatorNameValidator->getMessages());
      $this->addError(new Error($requestKey, $creatorName, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param mixed|string $prepare
   * @param string       $requestKey
   *
   * @return bool
   */
  protected function validatePrepare($prepare, $requestKey)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    if (!$stringLengthValidator->isValid(trim($prepare))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($requestKey, $prepare, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param mixed|array $info
   * @param string      $requestKey
   *
   * @return bool
   */
  protected function validateInfo($info, $requestKey)
  {
    $isArrayValidator = new IsArrayValidator;
    if (!$isArrayValidator->isValid($info)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error($requestKey, $info, $messages));
      return false;
    }
    return true;
  }
}
