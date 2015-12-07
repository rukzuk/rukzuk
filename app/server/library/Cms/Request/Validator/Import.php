<?php
namespace Cms\Request\Validator;

use Cms\Business\Import\Latch as LatchBusiness;
use Cms\Business\Export as ExportBusiness;
use Cms\Request\Import as Request;
use Cms\Request\Validator\Base;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\ModuleId as ModuleIdValidator;
use Cms\Validator\Integer as IntegerValidator;
use Cms\Validator\IsArray as IsArrayValidator;
use Orm\Data\Site as DataWebsite;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Modul as DataModul;
use Orm\Data\TemplateSnippet as DataTemplateSnippet;
use Orm\Data\Media as DataMedia;
use Seitenbau\Registry;
use \Zend_Validate_StringLength as StringLengthValidator;
use \Zend_Validate_NotEmpty as NotEmptyValidator;

/**
 * Import request validator
 *
 * @package      Cms
 * @subpackage   Request\Validator
 */
class Import extends Base
{
  private $expectedAllowedTypes = array(
      ExportBusiness::EXPORT_MODE_WEBSITE,
      ExportBusiness::EXPORT_MODE_MODULE,
      ExportBusiness::EXPORT_MODE_TEMPLATESNIPPET,
  );

  /**
   * @param \Cms\Request\Import\Overwrite $actionRequest
   */
  protected function validateMethodOverwrite(Request\Overwrite  $actionRequest)
  {
    $this->validateImportId($actionRequest->getImportId());
    if ($actionRequest->getTemplates() !== null) {
      $this->validateTemplateIds($actionRequest->getTemplates());
    }
    if ($actionRequest->getModules() !== null) {
      $this->validateModuleIds($actionRequest->getModules());
    }
    if ($actionRequest->getTemplateSnippets() !== null) {
      $this->validateTemplateSnippetIds($actionRequest->getTemplateSnippets(), 'templatesnippets');
    }
    if ($actionRequest->getMedia() !== null) {
      $this->validateMediaIds($actionRequest->getMedia());
    }
  }
  /**
   * @param \Cms\Request\Import\Cancel $actionRequest
   */
  protected function validateMethodCancel(Request\Cancel $actionRequest)
  {
    $this->validateImportId($actionRequest->getImportId());
  }
  /**
   * @param \Cms\Request\Import\File $actionRequest
   */
  protected function validateMethodFile(Request\File $actionRequest)
  {
    if ($actionRequest->getWebsiteId() !== \Cms\Request\Import\File::DEFAULT_EMPTY_WEBSITE_ID) {
      $this->validateWebsiteId($actionRequest->getWebsiteId());
    }
    $this->validateFileUpload($actionRequest->getUploadFilename());
    if ($actionRequest->getFileInputname() !== null) {
      $this->validateFileInputname($actionRequest->getFileInputname());
      $this->validateUploadFilenameExtension($actionRequest->getUploadFilename());
    }
    if ($actionRequest->getAllowedType() !== null) {
      $this->validateAllowedType($actionRequest->getAllowedType(), 'allowedType');
    }
  }
  /**
   * @param \Cms\Request\Import\Url $actionRequest
   */
  protected function validateMethodUrl(Request\Url $actionRequest)
  {
    if ($actionRequest->getWebsiteId() !== \Cms\Request\Import\Url::DEFAULT_EMPTY_WEBSITE_ID) {
      $this->validateWebsiteId($actionRequest->getWebsiteId());
    }
    $this->validateImportUrl($actionRequest->getUrl());
    if ($actionRequest->getAllowedType() !== null) {
      $this->validateAllowedType($actionRequest->getAllowedType(), 'allowedType');
    }
  }
  /**
   * @param \Cms\Request\Import\LocalFiles $actionRequest
   */
  protected function validateMethodLocalFiles(Request\LocalFiles $actionRequest)
  {
    $this->validateLocaleFileId($actionRequest->getProperty('localid'), 'localid');
    if ($actionRequest->hasProperty('websiteid')) {
      $this->validateWebsiteId($actionRequest->getProperty('websiteid'));
    }
    if ($actionRequest->hasProperty('allowedtype')) {
      $this->validateAllowedType($actionRequest->getProperty('allowedtype'), 'allowedtype');
    }
    if ($actionRequest->hasProperty('websitename')) {
      $this->validateWebsiteName($actionRequest->getProperty('websitename'), 'websitename');
    }
  }
  /**
   * @param  mixed $ids
   * @return boolean
   */
  private function validateMediaIds($ids)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $isArrayValidator->setMessage(
        "'%value%' ist kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('media', $ids, $messages));
      return false;
    }
    if (is_array($ids) && count($ids) > 0) {
      $mediaIdValidator = new UniqueIdValidator(
          DataMedia::ID_PREFIX,
          DataMedia::ID_SUFFIX
      );

      $mediaIdValidator->setMessage(
          "Media Id ist ungueltig",
          UniqueIdValidator::INVALID
      );
      
      foreach ($ids as $id) {
        if (!$mediaIdValidator->isValid($id)) {
          $messages = array_values($mediaIdValidator->getMessages());
          $this->addError(new Error('media', $id, $messages));
          return false;
        }
      }
      return true;
    }
    return true;
  }
  /**
   * @param mixed $ids
   * @return boolean
   */
  private function validateTemplateSnippetIds($ids, $field)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $isArrayValidator->setMessage(
        "'%value%' ist kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error($field, $ids, $messages));
      return false;
    }
    if (is_array($ids) && count($ids) > 0) {
      $templateSnippetIdValidator = new UniqueIdValidator(
          DataTemplateSnippet::ID_PREFIX,
          DataTemplateSnippet::ID_SUFFIX
      );

      $templateSnippetIdValidator->setMessage(
          "TemplateSnippet Id ist ungueltig",
          UniqueIdValidator::INVALID
      );
      
      foreach ($ids as $id) {
        if (!$templateSnippetIdValidator->isValid($id)) {
          $messages = array_values($templateSnippetIdValidator->getMessages());
          $this->addError(new Error($field, $id, $messages));
          return false;
        }
      }
      return true;
    }
    return true;
  }
  /**
   * @param mixed $ids
   * @return boolean
   */
  private function validateModuleIds($ids)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $isArrayValidator->setMessage(
        "'%value%' ist kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('modules', $ids, $messages));
      return false;
    }
    if (is_array($ids) && count($ids) > 0) {
      $modulIdValidator = new ModuleIdValidator(true);
      
      $modulIdValidator->setMessage(
          "Modul Id ist ungueltig",
          ModuleIdValidator::INVALID
      );
      
      foreach ($ids as $id) {
        if (!$modulIdValidator->isValid($id)) {
          $messages = array_values($modulIdValidator->getMessages());
          $this->addError(new Error('modules', $id, $messages));
          return false;
        }
      }
      return true;
    }
    return true;
  }
  /**
   * @param mixed $ids
   * @return boolean
   */
  private function validateTemplateIds($ids)
  {
    $isArrayValidator = new IsArrayValidator(false);
    $isArrayValidator->setMessage(
        "'%value%' ist kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    if (!$isArrayValidator->isValid($ids)) {
      $messages = array_values($isArrayValidator->getMessages());
      $this->addError(new Error('templates', $ids, $messages));
      return false;
    }
    if (is_array($ids) && count($ids) > 0) {
      $templateIdValidator = new UniqueIdValidator(
          DataTemplate::ID_PREFIX,
          DataTemplate::ID_SUFFIX
      );

      $templateIdValidator->setMessage(
          "Template Id ist ungueltig",
          UniqueIdValidator::INVALID
      );
      
      foreach ($ids as $id) {
        if (!$templateIdValidator->isValid($id)) {
          $messages = array_values($templateIdValidator->getMessages());
          $this->addError(new Error('templates', $id, $messages));
          return false;
        }
      }
      return true;
    }
    return true;
  }

  /**
   * @param string $filename
   */
  private function validateFileUpload($filename)
  {
    $fileUploadValidator = \Seitenbau\Validate\File\UploadFactory::getValidator();
    if (!$fileUploadValidator->isValid($filename)) {
      $messages = array_values($fileUploadValidator->getMessages());
      $this->addError(new Error('fileupload', $filename, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param  mixed $id
   * @return boolean
   */
  private function validateImportId($id)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => LatchBusiness::LATCH_IMPORT_ID_LENGTH,
      'max' => LatchBusiness::LATCH_IMPORT_ID_LENGTH
    ));
    $stringLengthValidator->setMessage(
        'Import id zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Import id zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($id))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('importid', $id, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param string   $name
   * @return boolean
   */
  private function validateFileInputname($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 2,
      'max' => 50
    ));
    $stringLengthValidator->setMessage(
        'File input name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'File input name zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($name))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('fileinputname', $name, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param string   $name
   * @return boolean
   */
  private function validateUploadFilenameExtension($name)
  {
    $config = Registry::getConfig();
    
    if (!isset($config->import->allowed->types)) {
      $messages = array('Allowed import file types not configured');
      $this->addError(new Error('fileinputextension', $name, $messages));
      return false;
    }
    
    $configuredImportExtensionsAsString = $config->import->allowed->types;
    
    $configuredImportExtensions = explode(',', $configuredImportExtensionsAsString);
    $configuredImportExtensions = array_map('trim', $configuredImportExtensions);
    
    if (!is_array($configuredImportExtensions) ||
        count($configuredImportExtensions) === 0 ||
        $configuredImportExtensions[0] === '') {
      $messages = array('Allowed import file extensions not configured');
      $this->addError(new Error('fileinputextension', $name, $messages));
      return false;
    }
    
    $uploadFilenameParts = explode('.', $name);
    $uploadFilenameExtension = end($uploadFilenameParts);
    
    if (!in_array($uploadFilenameExtension, $configuredImportExtensions)) {
      $message = sprintf(
          "Import file extension '%s' is not in configured allowed extension(s) [%s]",
          $uploadFilenameExtension,
          implode(',', $configuredImportExtensions)
      );
      $this->addError(new Error('fileinputextension', $name, array($message)));
      return false;
    }
    
    return true;
  }
  /**
   * @param string   $url
   * @return boolean
   */
  private function validateImportUrl($url)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 6
    ));
    $stringLengthValidator->setMessage(
        'Url zu kurz',
        StringLengthValidator::TOO_SHORT
    );

    if (!$stringLengthValidator->isValid(trim($url))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('url', $url, $messages));
      return false;
    }
    return true;
  }
  /**
   * @param integer $allowedType
   * @return boolean
   */
  private function validateAllowedType($allowedType, $field)
  {
    $typeValidator = new \Zend_Validate_InArray($this->expectedAllowedTypes);
    $typeValidator->setMessage(
        "'%value%' ist kein unterstuetzter Import-Typ'",
        \Zend_Validate_InArray::NOT_IN_ARRAY
    );

    if (!$typeValidator->isValid($allowedType)) {
      $messages = array_values($typeValidator->getMessages());
      $this->addError(new Error($field, $allowedType, $messages));
      return false;
    }

    return true;
  }

  /**
   * @param string $localId
   * @param string $field
   *
   * @return bool
   */
  private function validateLocaleFileId($localId, $field)
  {
    $localFileIdValidator = new StringLengthValidator(array(
      'min' => 1,
    ));
    if (!$localFileIdValidator->isValid($localId)) {
      $messages = array_values($localFileIdValidator->getMessages());
      $this->addError(new Error($field, $localId, $messages));
      return false;
    }
    return true;
  }

  /**
   * validate the website name
   *
   * $param string  name
   * @return boolean
   */
  private function validateWebsiteName($name)
  {
    $stringValidator = new \Zend_Validate_StringLength(array(
      'min' => 1,
      'max' => 255,
    ));
    if (!$stringValidator->isValid($name)) {
      $messages = array_values($stringValidator->getMessages());
      $this->addError(new Error('name', $name, $messages));
      return false;
    }
    return true;
  }
}
