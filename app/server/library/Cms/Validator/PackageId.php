<?php


namespace Cms\Validator;

/**
 * Class PackageId
 *
 * @package Cms\Validator
 */
class PackageId extends PhpClassNameId
{
  /**
   * @var array
   */
  protected $_messageTemplates = array(
    self::INVALID => "Invalid package id '%value%' given.",
  );
}
