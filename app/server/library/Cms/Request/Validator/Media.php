<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Media as Request;
use Orm\Data\Media as DataMedia;
use Orm\Data\Site as DataWebsite;
use Orm\Data\Album as DataAlbum;
use Dual\Media\Type as MediaType;
use Cms\Validator\IsArray as IsArrayValidator;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\Integer as IntegerValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use \Zend_Validate_Alpha as AlphaValidator;
use \Zend_Validate_InArray as InArrayValidator;
use \Zend_Validate_NotEmpty as NotEmptyValidator;
use Cms\Request\Validator\Error;

/**
 * Media request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */

class Media extends Base
{
  /**
   * @param \Cms\Request\Media\BatchMove $actionRequest
   */
  public function validateMethodBatchMove(Request\BatchMove $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateAlbumId($actionRequest->getAlbumId());
    if ($this->validateMediaIdsComeInAnArray($actionRequest->getIds())) {
      foreach ($actionRequest->getIds() as $mediaId) {
        $this->validateMediaId($mediaId);
      }
    }
  }
  /**
   * @param \Cms\Request\Media\GetAll $actionRequest
   */
  public function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateFilterParameters($actionRequest);

    if ($actionRequest->getAlbumId() !== null) {
      $this->validateAlbumId($actionRequest->getAlbumId());
    }

    if ($actionRequest->getType() !== null) {
      $this->validateType($actionRequest->getType());
    }

  }

  /**
   * @param \Cms\Request\Media\GetById $actionRequest
   */
  public function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateMediaId($actionRequest->getId());
  }

  /**
   * @param \Cms\Request\Media\GetMultipleByIds $actionRequest
   */
  public function validateMethodGetMultipleByIds(Request\GetMultipleByIds $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($this->validateMediaIdsComeInAnArray($actionRequest->getIds())) {
      foreach ($actionRequest->getIds() as $mediaId) {
        $this->validateMediaId($mediaId);
      }
    }
  }

  /**
   * @param \Cms\Request\Media\Upload $actionRequest
   */
  protected function validateMethodUpload(Request\Upload $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateMediaName($actionRequest->getName());
    $this->validateFileInputname($actionRequest->getFileInputname());
    $this->validateFileUpload($actionRequest->getUploadFilename());
    if ($actionRequest->getId() !== null) {
      $this->validateMediaId($actionRequest->getId());
    }
    if ($actionRequest->getAlbumId() !== null || $actionRequest->getId() === null) {
      $this->validateAlbumId($actionRequest->getAlbumId());
    }
  }

  /**
   * @param Cms\Request\Media\Delete $actionRequest
   */
  protected function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateMediaIdsComeInAnArray($actionRequest->getIds());
    foreach ($actionRequest->getIds() as $mediaId) {
      $this->validateMediaId($mediaId);
    }
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }

  /**
   * @param \Cms\Request\Media\Edit $actionRequest
   */
  protected function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateMediaId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getName() !== null) {
      $this->validateMediaName($actionRequest->getName());
    }
    if ($actionRequest->getAlbumId() !== null) {
      $this->validateAlbumId($actionRequest->getAlbumId());
    }
  }

  /**
   * @param \Cms\Request\Media\GetByFilter $actionRequest
   */
  protected function validateMethodGetByFilter(Request\GetByFilter $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateFilterParameters($actionRequest);
  }

  /**
   * @param Request\GetAll|Request\GetByFilter $actionRequest
   */
  protected function validateFilterParameters($actionRequest)
  {
    if ($actionRequest->getMaxIconwidth() !== null) {
      $this->validateBothMaxIconValuesAreSet(
          $actionRequest->getMaxIconwidth(),
          $actionRequest->getMaxIconheight()
      );
      $this->validateNumericFilterValue(
          array('maxiconwidth' => $actionRequest->getMaxIconwidth())
      );
    }

    if ($actionRequest->getMaxIconheight() !== null) {
      $this->validateBothMaxIconValuesAreSet(
          $actionRequest->getMaxIconwidth(),
          $actionRequest->getMaxIconheight()
      );
      $this->validateNumericFilterValue(
          array('maxiconheight' => $actionRequest->getMaxIconheight())
      );
    }

    if ($actionRequest->getLimit() !== null) {
      $this->validateNumericFilterValue(
          array('limit' => $actionRequest->getLimit())
      );
    }

    if ($actionRequest->getStart() !== null) {
      $this->validateNumericFilterValue(
          array('start' => $actionRequest->getStart())
      );
    }

    if ($actionRequest->getSort() !== null) {
      $this->validateFilterSort($actionRequest->getSort());
    }

    if ($actionRequest->getDirection() !== null) {
      $this->validateFilterDirection($actionRequest->getDirection());
    }

    if ($actionRequest->getSearch() !== null) {
      $this->validateSearch($actionRequest->getSearch());
    }
  }

  /**
   * @param  mixed $width
   * @param  mixed $height
   * @return boolean
   */
  private function validateBothMaxIconValuesAreSet($width, $height)
  {
    $maxIconValidator = new NotEmptyValidator(NotEmptyValidator::NULL);

    if ($maxIconValidator->isValid($width) && !$maxIconValidator->isValid($height)) {
      $maxIconValidator->setMessage(
          "'maxiconheight' can not be empty when 'maxiconwidth' is set",
          NotEmptyValidator::IS_EMPTY
      );
      $messages = array_values($maxIconValidator->getMessages());
      $this->addError(new Error('maxiconheight', $height, $messages));

      return false;
    }
    if (!$maxIconValidator->isValid($width) && $maxIconValidator->isValid($height)) {
      $maxIconValidator->setMessage(
          "'maxiconwidth' can not be empty when 'maxiconheight' is set",
          NotEmptyValidator::IS_EMPTY
      );
      $messages = array_values($maxIconValidator->getMessages());
      $this->addError(new Error('maxiconwidth', $width, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param string $type
   * @return boolean
   */
  private function validateType($type)
  {
    $inArrayValidator = new InArrayValidator(
        array(
        MediaType::TYPE_IMAGE,
        MediaType::TYPE_DOWNLOAD,
        MediaType::TYPE_MULTIMEDIA,
        MediaType::TYPE_MISC
        )
    );
    $allowedTypeValues = implode(', ', $inArrayValidator->getHaystack());
    $message = "Type '%value%' enthält keinen der folgenden "
      . "gültigen Werte (${allowedTypeValues})";

    $inArrayValidator->setMessage(
        $message,
        InArrayValidator::NOT_IN_ARRAY
    );

    if (!$inArrayValidator->isValid($type)) {
      $messages = array_values($inArrayValidator->getMessages());
      $this->addError(new Error('type', $type, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param string $id
   * @return boolean
   */
  private function validateAlbumId($id)
  {
    $albumIdValidator = new UniqueIdValidator(
        DataAlbum::ID_PREFIX,
        DataAlbum::ID_SUFFIX
    );

    if (!$albumIdValidator->isValid($id)) {
      $messages = array_values($albumIdValidator->getMessages());
      $this->addError(new Error('albumid', $id, $messages));
      return false;
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
   * @param  string  $sort
   * @return boolean
   */
  private function validateFilterSort($sort)
  {
    $alphaValidator = new AlphaValidator;
    $alphaValidator->setMessage(
        "Filter sort ist kein String",
        AlphaValidator::INVALID
    );
    $alphaValidator->setMessage(
        "Filter sort '%value%' enthält nicht alphabetische Character",
        AlphaValidator::NOT_ALPHA
    );

    if (!$alphaValidator->isValid($sort)) {
      $messages = array_values($alphaValidator->getMessages());
      $this->addError(new Error('sort', $sort, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param string   $direction
   * @return boolean
   */
  private function validateFilterDirection($direction)
  {
    $inArrayValidator = new InArrayValidator(
        array('ASC', 'DESC', 'asc', 'desc')
    );
    $allowedDirectionValues = implode(', ', $inArrayValidator->getHaystack());
    $message = "Filter direction '%value%' enthält keinen der folgenden "
      . "gültigen Werte (${allowedDirectionValues})";

    $inArrayValidator->setMessage(
        $message,
        InArrayValidator::NOT_IN_ARRAY
    );

    if (!$inArrayValidator->isValid($direction)) {
      $messages = array_values($inArrayValidator->getMessages());
      $this->addError(new Error('direction', $direction, $messages));

      return false;
    }

    return true;
  }

  /**
   * @param array $filterKeyValue
   * @return boolean
   */
  private function validateNumericFilterValue($filterKeyValue)
  {
    $filterKeys = array_keys($filterKeyValue);
    $filterKey = $filterKeys[0];
    $filterValues = array_values($filterKeyValue);
    $filterValue = $filterValues[0];

    $integerValidator = new IntegerValidator;
    $integerValidator->setMessage(
        "${filterKey} '%value%' ist keine Zahl",
        IntegerValidator::INVALID
    );

    if (!$integerValidator->isValid($filterValue)) {
      $messages = array_values($integerValidator->getMessages());
      $this->addError(new Error($filterKey, $filterValue, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param mixed
   * @return boolean
   */
  private function validateMediaIdsComeInAnArray($ids)
  {
    $isArrayValidator = new IsArrayValidator;
    $isArrayValidator->setMessage(
        "Media ids '%value%' sind kein Array",
        IsArrayValidator::INVALID_NO_ARRAY
    );
    $isArrayValidator->setMessage(
        "Angegebene Media ids '%value%' sind ein leerer Array",
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
  private function validateMediaId($id)
  {
    $mediaIdValidator = new UniqueIdValidator(
        DataMedia::ID_PREFIX,
        DataMedia::ID_SUFFIX
    );

    if (!$mediaIdValidator->isValid($id)) {
      $messages = array_values($mediaIdValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }

  /**
   *
   * @param  string  $search
   * @return boolean
   */
  private function validateSearch($search)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Search ist zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Search ist zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($search))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('search', $search, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string $name
   * @return boolean
   */
  private function validateMediaName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 2,
      'max' => 255
    ));
    $stringLengthValidator->setMessage(
        'Media Item name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Modul Item name zu lang',
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
}
