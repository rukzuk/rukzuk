<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Album as Request;
use Orm\Data\Album as DataAlbum;
use Orm\Data\Site as DataWebsite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use \Zend_Validate_StringLength as StringLengthValidator;
use Cms\Request\Validator\Error;

/**
 * Album request validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class Album extends Base
{

  /**
   * @param Cms\Request\Album\Delete $actionRequest
   */
  public function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateAlbumId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }
  /**
   * @param Cms\Request\Album\GetAll $actionRequest
   */
  public function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }
  /**
   * @param Cms\Request\Album\Create $actionRequest
   */
  public function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateName($actionRequest->getName());
  }
  /**
   * @param Cms\Request\Album\Edit $actionRequest
   */
  public function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateAlbumId($actionRequest->getId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateName($actionRequest->getName());
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
      $this->addError(new Error('id', $id, $messages));
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
        'Album name zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Album name zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($name))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('name', $name, $messages));
      return false;
    }
    return true;
  }
}
