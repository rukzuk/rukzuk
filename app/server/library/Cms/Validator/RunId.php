<?php
namespace Cms\Validator;

use Cms\Validator\UniqueId as UniqueIdValidator;

/**
 * UniqueId Validator
 *
 * @package      Cms
 * @subpackage   Validator
 */
class RunId extends UniqueIdValidator
{
  const PREFIX  = 'CMSRUNID-';
  const SUFFIX  = '-CMSRUNID';

  /**
   */
  public function __construct()
  {
    parent::__construct(self::PREFIX, self::SUFFIX);
  }
}
