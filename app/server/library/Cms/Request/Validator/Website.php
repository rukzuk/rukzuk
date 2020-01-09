<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Website as Request;
use Orm\Data\Site as DataSite;
use Orm\Data\Page as DataPage;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\Boolean as BooleanValidator;
use Cms\Request\Validator\Error;
use Cms\Validator\Resolutions;

/**
 * Website request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Website extends Base
{
  /**
   * validate the getbyid action request
   *
   * @param \Cms\Request\Website\GetById $actionRequest
   */
  protected function validateMethodGetById(Request\GetById $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');
  }

  /**
   * @param \Cms\Request\Website\Edit $actionRequest
   */
  protected function validateMethodEdit(Request\Edit $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');
    $this->validateCreateOrEditBaseParameters($actionRequest);

    if ($actionRequest->getName() != '') {
      $this->validateWebsiteName($actionRequest->getName());
    }
  }

  /**
   * @param \Cms\Request\Website\EditColorscheme $actionRequest
   */
  protected function validateMethodEditColorscheme(Request\EditColorscheme $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');

    if ($actionRequest->getColorscheme() != null) {
      $this->validateColorscheme($actionRequest->getColorscheme());
    }
  }

  /**
   * @param \Cms\Request\Website\EditResolutions $actionRequest
   */
  protected function validateMethodEditResolutions(Request\EditResolutions $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');

    if ($actionRequest->getResolutions() != null) {
      $this->validateResolutions($actionRequest->getResolutions(), 'resolutions');
    }
  }

  /**
   * @param Request\Create $actionRequest
   */
  protected function validateMethodCreate(Request\Create $actionRequest)
  {
    $this->validateWebsiteName($actionRequest->getName());
    $this->validateCreateOrEditBaseParameters($actionRequest);
  }

  /**
   * @param Request\Create|Request\Edit $actionRequest
   */
  protected function validateCreateOrEditBaseParameters($actionRequest)
  {
    if ($actionRequest->getPublishingEnabled() != null) {
      $this->validateBoolean($actionRequest->getPublishingEnabled(), 'publishingenabled');
    }

    if ($actionRequest->getPublish() != '') {
      $this->validatePublish($actionRequest->getPublish());
    }

    if ($actionRequest->getColorscheme() != '') {
      $this->validateColorscheme($actionRequest->getColorscheme());
    }

    if ($actionRequest->getResolutions() != '') {
      $this->validateResolutions($actionRequest->getResolutions(), 'resolutions');
    }

    if ($actionRequest->getHome() != '') {
      $this->validateHome($actionRequest->getHome());
    }
  }

  /**
   * @param \Cms\Request\Website\Copy $actionRequest
   */
  protected function validateMethodCopy(Request\Copy $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');
    $this->validateWebsiteName($actionRequest->getName());
  }

  /**
   * @param \Cms\Request\Website\Delete $actionRequest
   */
  protected function validateMethodDelete(Request\Delete $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateWebsiteId($actionRequest->getId(), 'id');
  }

  /**
   * @param \Cms\Request\Website\DisablePublishing $actionRequest
   */
  protected function validateMethodDisablePublishing(Request\DisablePublishing $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateWebsiteId($actionRequest->getId(), 'id');
  }

  /**
   * @param \Cms\Request\Website\Export $actionRequest
   */
  protected function validateMethodExport(Request\Export $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getId(), 'id');
  }

  /**
   * @param \Cms\Request\Website\UpdateContent $actionRequest
   */
  protected function validateMethodUpdateContent(Request\UpdateContent $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getProperty('websiteid', 'websiteid'));
  }

  /**
   * @param \Cms\Request\Page\Lock $actionRequest
   */
  protected function validateMethodLock(Request\Lock $actionRequest)
  {
    $this->validateRunId($actionRequest->getRunId());
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'websiteid');
    $this->validateBoolean($actionRequest->getOverride(), 'override');
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
        'max' => 255, 'min' => 3
    ));
    if (!$stringValidator->isValid($name)) {
      $messages = array_values($stringValidator->getMessages());
      $this->addError(new Error('name', $name, $messages));
      return false;
    }
    return true;
  }

  /**
   * validiert den publish parameter
   *
   * @param string $publish
   */
  private function validatePublish($publish)
  {
    $jsonValidator = new \Cms\Validator\JsonStructure();

    if (!$jsonValidator->isValid($publish)) {
      $messages = array_values($jsonValidator->getMessages());
      $this->addError(new Error('publish', $publish, $messages));
      return false;
    }

    $publishArr = \Seitenbau\Json::decode($publish);
    $publishEntries = array('type', 'url', 'protocol', 'host', 'username', 'password', 'basedir', 'cname');


    if (!is_array($publishArr)) {
      $this->addError(new Error('publish', $publish, array('publish data is empty')));
      return false;
    }

    if (!isset($publishArr['type']) || !in_array($publishArr['type'], array('internal', 'external'))) {
      $this->addError(new Error('publish', $publish, array('publish type is not supported')));
      return false;
    }

    $errors = false;
    foreach ($publishArr as $publishKey => $publishValue) {
      if (!in_array($publishKey, $publishEntries)) {
        $this->addError(new Error('publish', $publishKey, array('key \'' . $publishKey . '\' is not allowed')));
        $errors = true;
      }

      // alle keys muessen string sein
      if (!is_string($publishValue)) {
        $this->addError(new Error('publish', $publishValue, array('value \'' . $publishValue . '\' is not allowed')));
        $errors = true;
      }
    }


    if ($errors == true) {
      return false;
    }

    return true;
  }

  /**
   * validiert den colorscheme parameter
   *
   * @param string $colorscheme
   * @return  boolean
   */
  private function validateColorscheme($colorscheme)
  {
    $jsonValidator = new \Cms\Validator\JsonStructure();

    if (!$jsonValidator->isValid($colorscheme)) {
      $messages = array_values($jsonValidator->getMessages());
      $this->addError(new Error('colorscheme', $colorscheme, $messages));
      return false;
    }

    $colorschemeEntries = array('id', 'value', 'name');
    $colorschemeArr = \Seitenbau\Json::decode($colorscheme);

    if (!is_array($colorschemeArr)) {
      $this->addError(new Error('colorscheme', $colorscheme, array('wrong format')));
      return true;
    }

    $errors = false;
    if (is_array($colorschemeArr)) {
      foreach ($colorschemeArr as $colorschemeEntry) {
        foreach ($colorschemeEntry as $colorschemeKey => $colorschemeValue) {
          if (!in_array($colorschemeKey, $colorschemeEntries)) {
            $this->addError(new Error('colorscheme', $colorschemeKey, array('key \'' . $colorschemeKey . '\' is not allowed')));
            $errors = true;
          }
          if (!is_string($colorschemeValue)) {
            $this->addError(new Error('colorscheme', $colorschemeValue, array('value \'' . $colorschemeValue . '\' is not allowed')));
            $errors = true;
          }
        }
      }
    }

    if ($errors == true) {
      return false;
    }

    return true;
  }

  /**
   * validat the resolution parameter
   *
   * @param string $resolutions
   * @param        $field
   *
   * @return  boolean
   */
  private function validateResolutions($resolutions, $field)
  {
    $resolutionValidator = new Resolutions();
    $resolutionValidator->setTranslator($this->getTranslator());
    if (!$resolutionValidator->isValid($resolutions)) {
      $messages = array_values($resolutionValidator->getMessages());
      $this->addError(new Error($field, $resolutions, $messages));
      return false;
    }

    return true;
  }

  /**
   * validate the website home
   *
   * $param string  home
   * @return boolean
   */
  private function validateHome($home)
  {
    $idValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$idValidator->isValid($home)) {
      $messages = array_values($idValidator->getMessages());
      $this->addError(new Error('home', $home, $messages));
      return false;
    }
    return true;
  }
}
