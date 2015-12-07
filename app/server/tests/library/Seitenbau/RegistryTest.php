<?php
namespace Seitenbau;

use Seitenbau\Registry as Registry,
    Seitenbau\Logger as Logger,
    Doctrine\ORM\Configuration as DoctrineConfiguration,
    Doctrine\ORM\EntityManager as DoctrineEntityManager;
/**
 * Komponententest für Seitenbau\Registry
 *
 * @package      Seitenbau
 * @subpackage   Registrytest
 */
class RegistryTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function setActionLoggerShouldSetActionLogger()
  {
    $this->assertInstanceOf('Seitenbau\Logger\Action', Registry::getActionLogger());
  }
  /**
   * @test
   * @group library
   */
  public function setLoggerShouldSetLogger()
  {
    $this->assertInstanceOf('Seitenbau\Logger', Registry::getLogger());
  }
  /**
   * @test
   * @group library
   */
  public function setConfigShouldSetConfig()
  {
    $this->assertInstanceOf('Zend_Config', Registry::getConfig());
  }
  /**
   * @test
   * @group library
   */
  public function setLocaleShouldSetLocale()
  {
    $this->assertInstanceOf('Zend_Locale', Registry::getLocale());
  }
  /**
   * @test
   * @group library
   */
  public function setEntityManagerShouldSetEntityManager()
  {
    $this->assertInstanceOf(
      'Doctrine\ORM\EntityManager',
      Registry::getEntityManager()
    );
  }
}