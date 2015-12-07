<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Cdn as Request;
use Orm\Data\Site as DataSite;
use Orm\Data\Page as DataPage;
use Orm\Data\Template as DataTemplate;
use Cms\Validator\BuildId as BuildIdValidator;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Request\Validator\Error;
use \Zend_Validate_StringLength as StringLengthValidator;

/**
 * Cdn request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Cdn extends Base
{
  /**
   * @param Cms\Request\Cdn\GetBuild $actionRequest
   */
  protected function validateMethodGetBuild(Request\GetBuild $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBuildId($actionRequest->getId(), 'id');

    if ($actionRequest->getName() !== null) {
      $this->validateName($actionRequest->getName());
    }
  }
  /**
   * validate the getscreen action request
   *
   * @param Cms\Request\Cdn\GetScreen $actionRequest
   */
  protected function validateMethodGetScreen(Request\GetScreen $actionRequest)
  {
    $this->validateType($actionRequest->getType());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateId($actionRequest->getId(), $actionRequest->getType());
    //$this->validateWidth($actionRequest->getWidth());
    //$this->validateHeight($actionRequest->getHeight());
  }

  /**
   * @param  int $id
   * @return boolean
   */
  private function validateBuildId($id, $key = 'buildid')
  {
    $buildIdValidator = new BuildIdValidator();

    if (!$buildIdValidator->isValid($id)) {
      $messages = array_values($buildIdValidator->getMessages());
      $this->addError(new Error($key, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param  string  $name
   * @return boolean
   */
  private function validateName($name)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 1,
      'max' => 255
    ));

    $tooShortMessage = sprintf("Name '%s' zu kurz", ucfirst($name));
    $stringLengthValidator->setMessage(
        $tooShortMessage,
        StringLengthValidator::TOO_SHORT
    );
    $tooLongMessage = sprintf("Name '%s' zu lang", ucfirst($name));
    $stringLengthValidator->setMessage(
        $tooLongMessage,
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
   * validiert den paramter tpye
   *
   * @param string $type
   * @return boolean
   */
  private function validateType($type)
  {
    $typeValidator = new \Zend_Validate_InArray(
        array('page', 'template', 'website')
    );

    if (!$typeValidator->isValid($type)) {
      $messages = array_values($typeValidator->getMessages());
      $this->addError(new Error('type', $type, $messages));
      return false;
    }
    return true;
  }

  /**
   * validate den paramter id
   *
   * @param string $id
   * @return boolean
   */
  private function validateId($id, $type)
  {
    // die ID ist kein Pflichtfeld -> Validierung nicht immer noetig
    $idRequired = true;
    if ($type == 'page') {
      $idValidator = new UniqueIdValidator(
          DataPage::ID_PREFIX,
          DataPage::ID_SUFFIX
      );
    } elseif ($type == 'template') {
      $idValidator = new UniqueIdValidator(
          DataTemplate::ID_PREFIX,
          DataTemplate::ID_SUFFIX
      );
    } else {
      $idRequired = false;
    }


    if ($idRequired && !$idValidator->isValid($id)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('id', $id, $messages));
      return false;
    }
    return true;
  }

  private function validateWidth($width)
  {
    throw new \Exception('Not implement method ' . __METHOD__);
  }

  private function validateHeight($height)
  {
    throw new \Exception('Not implement method ' . __METHOD__);
  }
}
