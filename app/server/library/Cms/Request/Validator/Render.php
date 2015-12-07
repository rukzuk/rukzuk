<?php
namespace Cms\Request\Validator;

use Cms\Request\Validator\Base;
use Cms\Request\Render as Request;
use Orm\Data\Template as DataTemplate;
use Orm\Data\Page as DataPage;
use Orm\Data\Site as DataSite;
use Cms\Validator\UniqueId as UniqueIdValidator;
use Cms\Validator\RenderMode as RenderModeValidator;
use Dual\Render\RenderContext as RenderContext;
use Cms\Request\Validator\Error as Error;

/**
 * Website request validator
 *
 * @package    Cms
 * @subpackage Request\Validator
 */

class Render extends Base
{
  /**
   * validate the template action request
   *
   * @param Cms\Request\Render\Template $actionRequest
   */
  protected function validateMethodTemplate(Request\Template $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId());

    if ($actionRequest->getTemplateId() != '') {
      $this->validateTemplateId($actionRequest->getTemplateId());
    }
    if ($actionRequest->getData() != '') {
      $this->validateData($actionRequest->getData());
    }
    if ($actionRequest->getMode() != '') {
      $this->validateMode($actionRequest->getMode());
    } else {
      $actionRequest->setMode(RenderContext::MODE_PREVIEW);
    }
    if ($actionRequest->getTemplateId() == ''
        && $actionRequest->getData() == '') {
      $this->addError(new Error('data', null, array("'data' or 'tempalteid' must be set")));
      $this->addError(new Error('tempalteid', null, array("'data' or 'tempalteid' must be set")));
    }
  }

  /**
   * validate the page action request
   *
   * @param Cms\Request\Render\Page $actionRequest
   */
  protected function validateMethodPage(Request\Page $actionRequest)
  {
    $this->validatePageId($actionRequest->getPageId());
    $this->validateWebsiteId($actionRequest->getWebsiteId());
    if ($actionRequest->getData() != '') {
      $this->validateData($actionRequest->getData());
    }
    if ($actionRequest->getMode() != '') {
      $this->validateMode($actionRequest->getMode());
    } else {
      $actionRequest->setMode(RenderContext::MODE_PREVIEW);
    }
  }

  private function validateData($data)
  {
    $notEmptyValidator = new \Zend_Validate_NotEmpty();

    if (!$notEmptyValidator->isValid(\Zend_Json::encode($data))) {
      $messages = array_values($notEmptyValidator->getMessages());
      $this->addError(new Error('data', \Zend_Json::encode($data), $messages));
      return false;
    }
    return true;
  }

  private function validateMode($mode)
  {
    $renderModeValidator = new RenderModeValidator();

    if (!$renderModeValidator->isValid($mode)) {
      $messages = array_values($renderModeValidator->getMessages());
      $this->addError(new Error('mode', $mode, $messages));
      return false;
    }
    
    return true;
  }

  /**
   * validate the template id
   *
   * @param int $id
   * @return boolean
   */
  private function validateTemplateId($id)
  {
    $templateIdValidator = new UniqueIdValidator(
        DataTemplate::ID_PREFIX,
        DataTemplate::ID_SUFFIX
    );

    if (!$templateIdValidator->isValid($id)) {
      $messages = array_values($templateIdValidator->getMessages());
      $this->addError(new Error('tempalteid', $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * validate the template id
   *
   * @param int $id
   * @return boolean
   */
  private function validatePageId($id)
  {
    $pageIdValidator = new UniqueIdValidator(
        DataPage::ID_PREFIX,
        DataPage::ID_SUFFIX
    );

    if (!$pageIdValidator->isValid($id)) {
      $messages = array_values($pageIdValidator->getMessages());
      $this->addError(new Error('pageid', $id, $messages));
      return false;
    }
    return true;
  }
}
