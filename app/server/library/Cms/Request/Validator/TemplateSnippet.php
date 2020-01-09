<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\TemplateSnippet as Request;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use Cms\Validator\IsArray as IsArrayValidator;
use Cms\Request\Validator\Error;

/**
 * TemplateSnippet request validator
 *
 * @package      Cms
 * @subpackage   Request\Validator
 */

class TemplateSnippet extends Base
{
  protected function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param Cms\Request\TemplateSnippet\GetById $actionRequest
   */
  protected function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateTemplateSnippetId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param Cms\Request\TemplateSnippet\Delete $actionRequest
   */
  protected function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateSnippetIds($actionRequest->getIds());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param Cms\Request\TemplateSnippet\Create $actionRequest
   */
  protected function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateTemplateSnippetName($actionRequest->getName());
    if ($actionRequest->getContent() != '') {
      $this->validateContent($actionRequest->getContent());
    }
  }

  /**
   * @param Cms\Request\TemplateSnippet\Edit $actionRequest
   */
  protected function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateSnippetId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getName() != '') {
      $this->validateTemplateSnippetName($actionRequest->getName());
    }
    if ($actionRequest->getContent() != '') {
      $this->validateContent($actionRequest->getContent());
    }
  }

  /**
   * @param  array  $ids
   * @return boolean
   */
  private function validateTemplateSnippetIds($ids)
  {
    if (!$this->validateTemplateSnippetIdsComeInAsArray($ids)) {
      return false;
    }

    $templateSnippetIdsValid = true;
    foreach ($ids as $id) {
      if (!$this->validateTemplateSnippetId($id, 'ids')) {
        $templateSnippetIdsValid = false;
      }
    }

    return $templateSnippetIdsValid;
  }

  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateTemplateSnippetId($id, $fieldName = 'id')
  {
    $templateSnippetIdValidator = new UniqueIdValidator(
        DataTemplateSnippet::ID_PREFIX,
        DataTemplateSnippet::ID_SUFFIX
    );

    if (!$templateSnippetIdValidator->isValid($id)) {
      $messages = array_values($templateSnippetIdValidator->getMessages());
      $this->addError(new Error($fieldName, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string $name
   * @return boolean
   */
  private function validateTemplateSnippetName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Snippet Name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Snippet Name zu lang',
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
   * validate the content attribute
   *
   * @param string $content
   * @return  boolean
   */
  private function validateContent($content)
  {
    $contentValidator = new \Cms\Validator\UnitArray(
        new \Orm\Data\TemplateSnippet\MUnit()
    );

    if (!$contentValidator->isValid($content)) {
      $messages = array_values($contentValidator->getMessages());
      $this->addError(new Error('content', \Seitenbau\Json::encode($content), $messages));
      return false;
    }
    return true;
  }
  
  /**
   * @param mixed
   * @return boolean
   */
  private function validateTemplateSnippetIdsComeInAsArray($ids)
  {
    $isArrayValidator = new IsArrayValidator;
    $isArrayValidator->setMessage(
        "Snippet ids '%value%' ist kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    $isArrayValidator->setMessage(
        "Angegebene Snippet ids '%value%' ist ein leerer Array",
        IsArrayValidator::INVALID_EMPTY_ARRAY
    );

    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('ids', $ids, $messages));
      return false;
    }
    return true;
  }
}
