<?php
namespace Seitenbau;

use Seitenbau\Registry as Registry;

class OptinCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   */
  public function defaultCodeLengthIsUsedWhenCodeLengthIsNotConfigured()
  {
    $config = Registry::getConfig(); 
    Registry::setConfig(new \Zend_Config(array()));
    
    $this->assertEquals(
      OptinCodeGenerator::DEFAULT_CODE_LENGTH,
      strlen(OptinCodeGenerator::generate())
    );
    Registry::setConfig($config);
  }
  /**
   * @test
   * @group library
   */
  public function configuredCodeLengthIsUsedWhenConfigured()
  {
    $configuredCodeLength = Registry::getConfig()->optin->code->length;
    
    $this->assertEquals(
      $configuredCodeLength,
      strlen(OptinCodeGenerator::generate())
    );
  }
}