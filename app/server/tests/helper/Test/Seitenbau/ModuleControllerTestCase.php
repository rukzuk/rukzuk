<?php
namespace Test\Seitenbau;

use Test\Seitenbau\ControllerTestCase;
use Orm\Data\Modul as OrmDataModule;

/**
 * ModuleControllerTestCase
 *
 * @package      Seitenbau
 */
abstract class ModuleControllerTestCase extends ControllerTestCase
{
  /**
   * @return array
   */
  public function invalidModuleIdsProvider()
  {
    return array(
      array(false, new \stdClass()),
      array(false, null),
      array(false, array('one')),
      array(false, 'CAPITALS'),
      array(false, '0_started_with_number'),
      array(false, 'capital_at_enD'),
      array(false, '#invalid_id\"\'()'),
      array(false, '125.5'),
      array(false, '125,5'),
      array(true, OrmDataModule::ID_PREFIX.'01234567-456789abcdef'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'01234567-89ab'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'456789abcdef'.OrmDataModule::ID_SUFFIX),
      array(true, OrmDataModule::ID_PREFIX.'01234567_89ab_cdef_0123_456789abcdef'.OrmDataModule::ID_SUFFIX),
    );
  }
}
