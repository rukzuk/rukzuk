<?php


namespace Cms\Request\Validator;

use Cms\Request\WebsiteSettings as Request;
use Cms\Validator\WebsiteSettingsId as WebsiteSettingsIdValidator;

/**
 * @package    Cms\Request\Validator
 */

class WebsiteSettings extends Base
{
  /**
   * @param \Cms\Request\WebsiteSettings\GetAll $actionRequest
   */
  protected function validateMethodGetAll(Request\GetAll $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'websiteid');
  }

  /**
   * @param \Cms\Request\WebsiteSettings\EditMultiple $actionRequest
   */
  protected function validateMethodEditMultiple(Request\EditMultiple $actionRequest)
  {
    $this->validateWebsiteId($actionRequest->getWebsiteId(), 'websiteid');
    $this->validateMultipleWebsiteSettings($actionRequest->getAllWebsiteSettings(), 'websitesettings');
  }

  /**
   * @param mixed $allWebsiteSettings
   * @param string $name
   *
   * @return bool
   */
  protected function validateMultipleWebsiteSettings($allWebsiteSettings, $name)
  {
    if (!is_array($allWebsiteSettings)) {
      $this->addError(new Error($name, null, array('wrong format')));
      return false;
    }

    $success = true;
    foreach ($allWebsiteSettings as $id => $websiteSettings) {
      if (!$this->validateWebsiteSettingsId($id, $name)) {
        $success = false;
      }
      if (!$this->validateWebsiteSettings($websiteSettings, $name)) {
        $success = false;
      }
    }

    return $success;
  }

  /**
   * @param string $id
   * @param string $name
   *
   * @return bool
   */
  protected function validateWebsiteSettingsId($id, $name)
  {
    $runIdValidator = new WebsiteSettingsIdValidator();
    if (!$runIdValidator->isValid($id)) {
      $messages = array_values($runIdValidator->getMessages());
      $this->addError(new Error($name, $id, $messages));
      return false;
    }
    return true;
  }

  /**
   * @param mixed $websiteSettings
   * @param string $name
   *
   * @return bool
   */
  protected function validateWebsiteSettings($websiteSettings, $name)
  {
    if (!is_object($websiteSettings)) {
      $this->addError(new Error('websitesettings', $name, array('wrong format')));
      return false;
    }
    return true;
  }
}
