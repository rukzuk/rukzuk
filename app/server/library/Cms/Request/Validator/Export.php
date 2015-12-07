<?php
namespace Cms\Request\Validator;

use Cms\Business\Export as ExportBusiness;
use Cms\Request\Validator\Base;
use Cms\Validator\IsArray as IsArrayValidator;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Cms\Request\Export as Request;
use Cms\Validator\Boolean as BooleanValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use Orm\Data\Modul as DataModul;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Page as DataPage;
use Orm\Data\Site as DataWebsite;
use Seitenbau\Types\Boolean as Boolean;

/**
 * Export request validator
 *
 * @package      Cms
 * @subpackage   Request\Validator
 */
class Export extends Base
{
  /**
   * @param \Cms\Request\Export\Module $actionRequest
   */
  protected function validateMethodModule(Request\Module $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());

    if ($this->validateIdsComeInAnArray($actionRequest->getIds())) {
      foreach ($actionRequest->getIds() as $moduleId) {
        $this->validateModuleId($moduleId);
      }
    }
    if ($actionRequest->getExportName() !== null) {
      $this->validateExportName($actionRequest->getExportName());
    }
  }

  /**
   * @param \Cms\Request\Export\TemplateSnippets $actionRequest
   */
  protected function validateMethodTemplateSnippets(Request\TemplateSnippets $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());

    if ($this->validateIdsComeInAnArray($actionRequest->getIds())) {
      foreach ($actionRequest->getIds() as $templateSnippetId) {
        $this->validateTemplateSnippetId($templateSnippetId);
      }
    }
    if ($actionRequest->getExportName() !== null) {
      $this->validateExportName($actionRequest->getExportName());
    }
  }

  /**
   * @param \Cms\Request\Export\Website $actionRequest
   */
  protected function validateMethodWebsite(Request\Website $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getExportName() !== null) {
      $this->validateExportName($actionRequest->getExportName());
    }
    $this->validateIsBoolean($actionRequest->getComplete(), 'complete');
  }

  /**
   * @param  string $name
   * @return boolean
   */
  private function validateExportName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 2,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Export Name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Export Name zu lang',
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
   * @param mixed
   * @return boolean
   */
  private function validateIdsComeInAnArray($ids)
  {
    $isArrayValidator = new IsArrayValidator;
    $isArrayValidator->setMessage(
        "Ids '%value%' sind kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    $isArrayValidator->setMessage(
        "Angegebene Ids '%value%' sind ein leerer Array",
        IsArrayValidator::INVALID_EMPTY_ARRAY
    );

    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('ids', $ids, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateModuleId($id)
  {
    $mediaIdValidator = new ModuleIdValidator(true);

    $mediaIdValidator->setMessage(
        "Angegebene Media Id ist ungueltig",
        ModuleIdValidator::INVALID
    );

    if (!$mediaIdValidator->isValid($id)) {
      $messages = array_values($mediaIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateTemplateSnippetId($id)
  {
    $templateSnippetIdValidator = new UniqueIdValidator(
        DataTemplateSnippet::ID_PREFIX,
        DataTemplateSnippet::ID_SUFFIX
    );

    $templateSnippetIdValidator->setMessage(
        "Angegebene Snippet Id ist ungueltig",
        UniqueIdValidator::INVALID
    );

    if (!$templateSnippetIdValidator->isValid($id)) {
      $messages = array_values($templateSnippetIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateTemplateId($id)
  {
    $templateIdValidator = new UniqueIdValidator(
        DataTemplate::ID_PREFIX,
        DataTemplate::ID_SUFFIX
    );

    $templateIdValidator->setMessage(
        "Angegebene Template Id ist ungueltig",
        UniqueIdValidator::INVALID
    );
    
    if (!$templateIdValidator->isValid($id)) {
      $messages = array_values($templateIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param  string  $id
   * @return boolean
   */
  private function validatePageId($id)
  {
    $pageIdValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    $pageIdValidator->setMessage(
        "Angegebene Page Id ist ungueltig",
        UniqueIdValidator::INVALID
    );

    if (!$pageIdValidator->isValid($id)) {
      $messages = array_values($pageIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }
}
