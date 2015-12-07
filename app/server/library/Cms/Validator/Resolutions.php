<?php


namespace Cms\Validator;

class Resolutions extends \Zend_Validate_Abstract
{
  const INVALID = 'error.validation.resolutions.resolution.no_json';
  const PROPERTY_ENABLED_MISSING = 'error.validation.resolutions.property.enabled.missing';
  const PROPERTY_ENABLED_NO_BOOLEAN = 'error.validation.resolutions.property.enabled.no_boolean';
  const PROPERTY_DATA_MISSING = 'error.validation.resolutions.property.data.missing';
  const PROPERTY_DATA_NO_ARRAY = 'error.validation.resolutions.property.data.no_array';
  const PROPERTY_DATA_WRONG_FORMAT  = 'error.validation.resolutions.property.data.wrong_format';
  const PROPERTY_DATA_ID_MISSING = 'error.validation.resolutions.property.data.id.missing';
  const PROPERTY_DATA_ID_NOT_A_STRING = 'error.validation.resolutions.property.data.id.no_string';
  const PROPERTY_DATA_ID_DUPLICATED = 'error.validation.resolutions.property.data.id.duplicated';
  const PROPERTY_DATA_NAME_MISSING = 'error.validation.resolutions.property.data.name.missing';
  const PROPERTY_DATA_WIDTH_MISSING = 'error.validation.resolutions.property.data.width.missing';
  const PROPERTY_DATA_WRONG_ORDER = 'error.validation.resolutions.property.data.width.wrong_order';

  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => self::INVALID,
    self::PROPERTY_ENABLED_MISSING => self::PROPERTY_ENABLED_MISSING,
    self::PROPERTY_ENABLED_NO_BOOLEAN => self::PROPERTY_ENABLED_NO_BOOLEAN,
    self::PROPERTY_DATA_MISSING => self::PROPERTY_DATA_MISSING,
    self::PROPERTY_DATA_NO_ARRAY => self::PROPERTY_DATA_NO_ARRAY,
    self::PROPERTY_DATA_WRONG_FORMAT => self::PROPERTY_DATA_WRONG_FORMAT,
    self::PROPERTY_DATA_ID_MISSING => self::PROPERTY_DATA_ID_MISSING,
    self::PROPERTY_DATA_ID_NOT_A_STRING => self::PROPERTY_DATA_ID_NOT_A_STRING,
    self::PROPERTY_DATA_ID_DUPLICATED => self::PROPERTY_DATA_ID_DUPLICATED,
    self::PROPERTY_DATA_NAME_MISSING => self::PROPERTY_DATA_NAME_MISSING,
    self::PROPERTY_DATA_WIDTH_MISSING => self::PROPERTY_DATA_WIDTH_MISSING,
    self::PROPERTY_DATA_WRONG_ORDER => self::PROPERTY_DATA_WRONG_ORDER,
  );

  /**
   * Returns true if and only if $value meets the validation requirements
   *
   * If $value fails validation, then this method returns false, and
   * getMessages() will return an array of messages that explain why the
   * validation failed.
   *
   * @param  mixed $resolutionsJson
   *
   * @return boolean
   * @throws Zend_Validate_Exception If validation of $value is impossible
   */
  public function isValid($resolutionsJson)
  {
    $this->_setValue($resolutionsJson);

    if (!is_string($resolutionsJson)) {
      $this->_error(self::INVALID);
      return false;
    }

    $resolutions = json_decode($resolutionsJson);
    if (!is_object($resolutions)) {
      $this->_error(self::INVALID);
      return false;
    }
    return $this->isValidResolutions($resolutions);
  }

  protected function isValidResolutions(\stdClass $resolutions)
  {
    $isValid = true;
    if (!$this->isValidPropertyEnabled($resolutions)) {
      $isValid = false;
    }
    if (!$this->isValidPropertyData($resolutions)) {
      $isValid = false;
    }
    return $isValid;
  }

  protected function isValidPropertyEnabled(\stdClass $resolutions)
  {
    if (!property_exists($resolutions, 'enabled')) {
      $this->_error(self::PROPERTY_ENABLED_MISSING);
      return false;
    }
    if (!is_bool($resolutions->enabled)) {
      $this->_error(self::PROPERTY_ENABLED_NO_BOOLEAN);
      return false;
    }
    return true;
  }

  protected function isValidPropertyData(\stdClass $resolutions)
  {
    if (!property_exists($resolutions, 'data')) {
      $this->_error(self::PROPERTY_DATA_MISSING);
      return false;
    }
    if (!is_array($resolutions->data)) {
      $this->_error(self::PROPERTY_DATA_NO_ARRAY);
      return false;
    }
    $lastWidth = null;
    $existingIds = array();
    foreach ($resolutions->data as $resolution) {
      if (!is_object($resolution)) {
        $this->_error(self::PROPERTY_DATA_WRONG_FORMAT);
        return false;
      }
      if (!property_exists($resolution, 'id')) {
        $this->_error(self::PROPERTY_DATA_ID_MISSING);
        return false;
      }
      if (!is_string($resolution->id)) {
        $this->_error(self::PROPERTY_DATA_ID_NOT_A_STRING);
        return false;
      }
      if (in_array($resolution->id, $existingIds)) {
        $this->_error(self::PROPERTY_DATA_ID_DUPLICATED);
        return false;
      }
      $existingIds[] = $resolution->id;
      if (!property_exists($resolution, 'name')) {
        $this->_error(self::PROPERTY_DATA_NAME_MISSING);
        return false;
      }
      if (!property_exists($resolution, 'width')) {
        $this->_error(self::PROPERTY_DATA_WIDTH_MISSING);
        return false;
      }
      if (!is_null($lastWidth) && $lastWidth < intval($resolution->width)) {
        $this->_error(self::PROPERTY_DATA_WRONG_ORDER, $resolution->name);
        return false;
      }
      $lastWidth = intval($resolution->width);
    }

    return true;
  }
}
