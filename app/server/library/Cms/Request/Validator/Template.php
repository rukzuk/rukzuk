<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Template as Request;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\JsonStructure as JsonStructureValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use \Cms\Validator\Boolean as BooleanValidator;
use Cms\Request\Validator\Error;
use Cms\Validator\PageTypeId as PageTypeIdValidator;

/**
 * Template request validator
 *
 * @package      Cms
 * @subpackage   Request\Validator
 */

class Template extends Base
{
  protected function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Template\GetById $actionRequest
   */
  protected function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateTemplateId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Template\DeleteById $actionRequest
   */
  protected function validateMethodDeleteById(Request\DeleteById $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Template\Create $actionRequest
   */
  protected function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateTemplateName($actionRequest->getName());
    $this->validatePageType($actionRequest->getPageType(), 'pagetype');
    if ($actionRequest->getContent() != '') {
      $this->validateContent($actionRequest->getContent());
    }
  }

  /**
   * @param \Cms\Request\Template\Edit $actionRequest
   */
  protected function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getName() != '') {
      $this->validateTemplateName($actionRequest->getName());
    }
    if ($actionRequest->getContent() != '') {
      $this->validateContent($actionRequest->getContent());
    }
  }

  /**
   * @param \Cms\Request\Template\EditMeta $actionRequest
   */
  protected function validateMethodEditMeta(Request\EditMeta $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getName() != '') {
      $this->validateTemplateName($actionRequest->getName());
    }
  }

  /**
   * @param \Cms\Request\Template\Lock $actionRequest
   */
  protected function validateMethodLock(Request\Lock $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateTemplateId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBoolean($actionRequest->getOverride(), 'override');
  }

  /**
   * @param  array  $ids
   * @return boolean
   */
  private function validateTemplateIds($ids)
  {
    if (!is_array($ids)) {
      $messages = array('IDs muessen als Array uebermittelt werden');
      $this->addError(new Error('ids', $ids, $messages));
      return false;
    }

    $templateIdsValid = true;
    foreach ($ids as $id) {
      if (!$this->validateTemplateId($id, 'ids')) {
        $templateIdsValid = false;
      }
    }

    return $templateIdsValid;
  }

  /**
   * @param  string  $id
   * @return boolean
   */
  private function validateTemplateId($id, $fieldName = 'id')
  {
    $templateIdValidator = new UniqueIdValidator(
        DataTemplate::ID_PREFIX,
        DataTemplate::ID_SUFFIX
    );

    if (!$templateIdValidator->isValid($id)) {
      $messages = array_values($templateIdValidator->getMessages());
      $this->addError(new Error($fieldName, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string $name
   * @return boolean
   */
  private function validateTemplateName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Template name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Template name zu lang',
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
        new \Orm\Data\Template\MUnit()
    );

    if (!$contentValidator->isValid($content)) {
      $messages = array_values($contentValidator->getMessages());
      $this->addError(new Error('content', \Seitenbau\Json::encode($content), $messages));
      return false;
    }
    return true;
  }

  private function validateOverwrite($overwrite)
  {
    return $this->validateBoolean($overwrite, 'overwrite');
  }

  /**
   * @param string $pageType
   * @param string $fieldName
   *
   * @return bool
   */
  private function validatePageType($pageType, $fieldName)
  {
    $pageTypeIdValidator = new PageTypeIdValidator();

    if (!$pageTypeIdValidator->isValid($pageType)) {
      $messages = array_values($pageTypeIdValidator->getMessages());
      $this->addError(new Error($fieldName, $pageType, $messages));
      return false;
    }
    return true;
  }
}
