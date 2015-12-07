<?php


namespace Cms\Validator;

/**
 * Class PageTypeId
 *
 * @package Cms\Validator
 */
class PageTypeId extends PhpClassNameId
{
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Invalid page type id '%value%' given.",
  );
}
