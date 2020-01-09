<?php
namespace Cms\Request\Validator;

use Cms\Request\Page as Request;
use Orm\Data\Page as DataPage;
use Orm\Data\Template as DataTemplate;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\UnitArray as UnitArrayValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use Cms\Validator\PageTypeId as PageTypeIdValidator;
use Cms\Validator\JsonStructure as JsonStructureValidator;

/**
 * page request validator
 *
 * @package    Cms
 * @subpackage Controller
 */

class Page extends Base
{
  /**
   * validate the getbyid action request
   *
   * @param \Cms\Request\Page\GetById $actionRequest
   */
  protected function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Page\Copy $actionRequest
   */
  protected function validateMethodCopy(Request\Copy $actionRequest)
  {
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateName($actionRequest->getName());
  }

  /**
   * @param \Cms\Request\Page\Delete $actionRequest
   */
  protected function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Page\Edit $actionRequest
   */
  protected function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());

    if ($actionRequest->getContent() !== null) {
      $this->validateContent($actionRequest->getContent());
    }
  }

  /**
   * @param \Cms\Request\Page\EditMeta $actionRequest
   */
  protected function validateMethodEditMeta(Request\EditMeta $actionRequest)
  {
    $this->validateRunId($actionRequest->getProperty('runid'));
    $this->validatePageId($actionRequest->getProperty('id'));
    $this->validateWebsiteId($actionRequest->getProperty('websiteid'));

    if ($actionRequest->hasProperty('name')) {
      $this->validateName($actionRequest->getProperty('name'));
    }
    if ($actionRequest->hasProperty('description')) {
      $this->validateDescription($actionRequest->getProperty('description'), 'description');
    }
    if ($actionRequest->hasProperty('innavigation')) {
      $this->validateInNavigation($actionRequest->getProperty('innavigation'));
    }
    if ($actionRequest->hasProperty('navigationtitle')) {
      $this->validateNavigationTitle($actionRequest->getProperty('navigationtitle'), 'navigationtitle');
    }
    if ($actionRequest->hasProperty('date')) {
      $this->validateDate($actionRequest->getProperty('date'));
    }
    if ($actionRequest->hasProperty('pageattributes')) {
      $this->validatePageAttributes($actionRequest->getProperty('pageattributes'), 'pageattributes');
    }
  }

  /**
   * @param \Cms\Request\Page\Move $actionRequest
   */
  protected function validateMethodMove(Request\Move $actionRequest)
  {
    // Pflichtparameter
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateParentId($actionRequest->getParentId());

    // Optionale Parameter
    if ($actionRequest->isPropertySet('insertBeforeId')) {
      $this->validateInsertBeforeId($actionRequest->getInsertBeforeId());
    }
  }

  /**
   * @param \Cms\Request\Page\Create $actionRequest
   */
  protected function validateMethodCreate(Request\Create $actionRequest)
  {
    // Pflichtparameter
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateTemplateId($actionRequest->getTemplateId());
    $this->validateName($actionRequest->getName());
    $this->validateParentId($actionRequest->getParentId());
    $this->validatePageType($actionRequest->getPageType(), 'pagetype');

    // Optionale Parameter
    if ($actionRequest->isPropertySet('description')) {
      $this->validateDescription($actionRequest->getDescription(), 'description');
    }
    if ($actionRequest->isPropertySet('date')) {
      $this->validateDate($actionRequest->getDate());
    }
    if ($actionRequest->isPropertySet('inNavigation')) {
      $this->validateInNavigation($actionRequest->getInNavigation());
    } else {
      $actionRequest->setInNavigation(true);
    }
    if ($actionRequest->isPropertySet('navigationTitle')) {
      $this->validateNavigationTitle($actionRequest->getNavigationTitle(), 'navigationtitle');
    }
    if ($actionRequest->isPropertySet('content')) {
      $this->validateContent($actionRequest->getContent());
    }
    if ($actionRequest->isPropertySet('insertBeforeId')) {
      $this->validateInsertBeforeId($actionRequest->getInsertBeforeId());
    }
    if ($actionRequest->getPageAttributes() !== null) {
      $this->validatePageAttributes($actionRequest->getPageAttributes(), 'pageattributes');
    }
  }

  /**
   * @param \Cms\Request\Page\GetAllPageTypes $actionRequest
   */
  protected function validateMethodGetAllPageTypes(Request\GetAllPageTypes $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Page\Lock $actionRequest
   */
  protected function validateMethodLock(Request\Lock $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validatePageId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBoolean($actionRequest->getOverride(), 'override');
  }

  /**
   * validiert die page id
   *
   * @param string $id
   * @return boolean
   */
  private function validatePageId($id)
  {
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$idValidator->isValid($id)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert den insertBeforeId parameter
   *
   * @param string $insertBeforeId
   * @return boolean
   */
  private function validateInsertBeforeId($insertBeforeId)
  {
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$idValidator->isValid($insertBeforeId)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('insertBeforeId', $insertBeforeId, $messages));
      return false;
    }
    return true;
  }

  /**
   * validatiert den page name
   *
   * @param string  name
   * @return boolean
   */
  private function validateName($name)
  {
    $stringValidator = new StringLengthValidator(array(
        'max' => 255, 'min' => 1
    ));
    if (!$stringValidator->isValid($name)) {
      $messages = array_values($stringValidator->getMessages());
      $this->addError(new Error('name', $name, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert die beschreibung
   *
   * @param string $description
   * @param string $fieldName
   * @return  boolean
   */
  private function validateDescription($description, $fieldName)
  {
    $stringLengthValidator = new StringLengthValidator();
    if (!$stringLengthValidator->isValid(trim($description))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($fieldName, $description, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert den inNavigation Parameter
   *
   * @param string $inNavigation
   * @return boolean
   */
  private function validateInNavigation($inNavigation)
  {
    if (!is_bool($inNavigation) && $inNavigation != '0' && $inNavigation != '1') {
      $messages = array('inNavigation is not boolean');
      $this->addError(new Error('inNavigation', $inNavigation, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert den naviagations titel parameter
   *
   * @param string $navigationTitle
   * @param string $fieldName
   * @return boolean
   */
  private function validateNavigationTitle($navigationTitle, $fieldName)
  {
    $stringLengthValidator = new StringLengthValidator(array('max' => 255));
    $stringLengthValidator->setMessage(
        $this->_('error.validation.page.navigationtitle.too_long'),
        StringLengthValidator::TOO_LONG
    );
    if (!$stringLengthValidator->isValid(trim($navigationTitle))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error($fieldName, $navigationTitle, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert den date parameter
   *
   * @param string $date
   * @return boolean
   */
  private function validateDate($date)
  {
    // TODO: Validierung des Datums
    \Seitenbau\Registry::getLogger()->log(
        __METHOD__,
        __LINE__,
        'TODO: Page Validierung Date',
        \Seitenbau\Log::DEBUG
    );

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
    $contentValidator = new UnitArrayValidator(new \Orm\Data\Page\MUnit());

    if (!$contentValidator->isValid($content)) {
      $messages = array_values($contentValidator->getMessages());
      $this->addError(new Error('content', \Seitenbau\Json::encode($content), $messages));
      return false;
    }
    return true;
  }

  /**
   * valiert den parentId Parameter
   */
  private function validateParentId($parentId)
  {
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$idValidator->isValid($parentId) && $parentId != 'root') {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('parentId', $parentId, $messages));
      return false;
    }
    return true;
  }

  private function validateTemplateId($templateId)
  {
    $idValidator = new UniqueIdValidator(
        DataTemplate::ID_PREFIX,
        DataTemplate::ID_SUFFIX
    );

    if (!$idValidator->isValid($templateId)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('templateId', $templateId, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param string $value
   * @param string $fieldName
   *
   * @return bool
   */
  protected function validatePageType($value, $fieldName)
  {
    $idValidator = new PageTypeIdValidator();
    if (!$idValidator->isValid($value)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error($fieldName, $value, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param string $value
   * @param string $fieldName
   *
   * @return bool
   */
  protected function validatePageAttributes($value, $fieldName)
  {
    $jsonValidator = new JsonStructureValidator();
    if (!$jsonValidator->isValid($value)) {
      $messages = array_values($jsonValidator->getMessages());
      $this->addError(new Error($fieldName, $value, $messages));
      return false;
    }
    return true;
  }
}
