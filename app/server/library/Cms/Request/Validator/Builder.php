<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Builder as Request;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\BuildId as BuildIdValidator;
use Cms\Validator\PublishedId as PublishedIdValidator;
use Cms\Request\Validator\Error;
use \Zend_Validate_StringLength as StringLengthValidator;

/**
 * Builder
 *
 * @package      Cms
 * @subpackage   Request\Validator
 */
class Builder extends Base
{
  const MAX_ZIP_COMMENT_LENGTH = 65535;
  
  /**
   * @param Cms\Request\Builder\PublishWebsite $actionRequest
   */
  protected function validateMethodPublishWebsite(Request\PublishWebsite $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBuildId($actionRequest->getBuildId(), 'id');
  }
  /**
   * @param Cms\Request\Builder\GetWebsiteBuilds $actionRequest
   */
  protected function validateMethodGetWebsiteBuilds(Request\GetWebsiteBuilds $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
  }
  /**
   * @param Cms\Request\Builder\GetWebsiteBuildById $actionRequest
   */
  protected function validateMethodGetWebsiteBuildById(Request\GetWebsiteBuildById $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBuildId($actionRequest->getBuildId());
  }
  /**
   * @param Cms\Request\Builder\BuildWebsite $actionRequest
   */
  protected function validateMethodBuildWebsite(Request\BuildWebsite $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getComment() !== null) {
      $this->validateComment($actionRequest->getComment());
    }
  }
  /**
   * @param Cms\Request\Builder\PublisherStatusChanged $actionRequest
   */
  protected function validateMethodPublisherStatusChanged(Request\PublisherStatusChanged $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    $this->validateBuildId($actionRequest->getBuildId());
    $this->validatePublishedId($actionRequest->getPublishedId());
  }

  /**
   * @param  string $comment
   * @return boolean
   */
  private function validateComment($comment)
  {
    $stringLengthValidator = new StringLengthValidator(array(
      'min' => 0,
      'max' => self::MAX_ZIP_COMMENT_LENGTH
    ));
    $stringLengthValidator->setMessage(
        'Build Kommentar zu kurz',
        StringLengthValidator::TOO_SHORT
    );
    $stringLengthValidator->setMessage(
        'Build Kommentar zu lang',
        StringLengthValidator::TOO_LONG
    );

    if (!$stringLengthValidator->isValid(trim($comment))) {
      $messages = array_values($stringLengthValidator->getMessages());
      $this->addError(new Error('comment', $comment, $messages));
      return false;
    }
    
    return true;
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
   * @param  int $id
   * @return boolean
   */
  private function validatePublishedId($id, $key = 'publishedid')
  {
    $publishedIdValidator = new PublishedIdValidator();
    
    if (!$publishedIdValidator->isValid($id)) {
      $messages = array_values($publishedIdValidator->getMessages());
      $this->addError(new Error($key, $id, $messages));
      return false;
    }
    return true;
  }
}
