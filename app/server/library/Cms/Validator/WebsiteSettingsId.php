<?php


namespace Cms\Validator;

/**
 * Class WebsiteSettingsId
 *
 * @package Cms\Validator
 */
class WebsiteSettingsId extends PhpClassNameId
{
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Invalid website settings id '%value%' given.",
  );
}
