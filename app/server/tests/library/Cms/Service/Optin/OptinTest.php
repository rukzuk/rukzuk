<?php
namespace Cms\Service\Optin;

use Cms\Service\Optin as OptinService,
    Seitenbau\Registry,
    Test\Seitenbau\Optin as OptinTestHelper,
    Test\Seitenbau\ServiceTestCase;
/**
 * OptinTest
 *
 * @package      Test
 * @subpackage   Service
 */
class OptinTest extends ServiceTestCase
{
  /**
   * @var \Cms\Service\Optin
   */
  protected $service;
  
  protected function setUp()
  {
    parent::setUp();

    $this->service = new OptinService('Optin');
  }
  /**
   * @test
   * @group library
   * @expectedException \Cms\Service\Optin\InvalidModeException
   * @expectedExceptionCode 1038
   */
  public function optinServiceShouldThrowExpectedExceptionOnInvalidMode()
  {
    $this->service->validateCode('g03bb64gpoi');
  }
  /**
   * @test
   * @group library
   * @expectedException \Cms\Service\Optin\ExpiredCodeException
   * @expectedExceptionCode 1037
   */
  public function optinServiceShouldThrowExpectedExceptionOnExpiredCode()
  {
    $this->service->validateCode('f01bb65grbw');
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Service\Optin\ExpiredCodeException
   * @expectedExceptionCode 1037
   */
  public function isValidTimeboxShouldThrowExceptionWhenLifetimeIsExpired()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      5
    );
    
    $optin = new \Orm\Entity\OptIn;
    $today = new \DateTime();
    $intervalSpec = sprintf(
      'P%dD', 
      Registry::getConfig()->optin->lifetime->register + 1
    );
    
    $optin->setTimestamp($today->sub(new \DateInterval($intervalSpec)));
    $optin->setMode(\Orm\Entity\OptIn::MODE_REGISTER);

    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    $isValidTimeboxMethod->setAccessible(true);
    $this->assertFalse(
      $isValidTimeboxMethod->invoke($this->service, $optin)
    );
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      $formerLifetime
    );
    
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      5
    );
    
    $optin = new \Orm\Entity\OptIn;
    $today = new \DateTime();
    
    $optin->setTimestamp($today->sub(new \DateInterval($intervalSpec)));
    $optin->setMode(\Orm\Entity\OptIn::MODE_PASSWORD);
    
    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    $isValidTimeboxMethod->setAccessible(true);
    $isValidTimeboxMethod->invoke($this->service, $optin);
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
  }
  /**
   * @test
   * @group library
   */
  public function isValidTimeboxShouldReturnTrueWhenLifetimeIsWithinBoundary()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      2
    );
    
    $optin = new \Orm\Entity\OptIn;
    $today = new \DateTime();
    $intervalSpec = sprintf('P%dD', 1);
    
    $optin->setTimestamp($today->sub(new \DateInterval($intervalSpec)));
    $optin->setMode(\Orm\Entity\OptIn::MODE_REGISTER);
    
    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    $isValidTimeboxMethod->setAccessible(true);
    $this->assertTrue(
      $isValidTimeboxMethod->invoke($this->service, $optin)
    );
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      $formerLifetime
    );
    
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      2
    );
    
    $optin = new \Orm\Entity\OptIn;
    $today = new \DateTime();
    
    $optin->setTimestamp($today->sub(new \DateInterval($intervalSpec)));
    $optin->setMode(\Orm\Entity\OptIn::MODE_PASSWORD);
    
    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    $isValidTimeboxMethod->setAccessible(true);
    $this->assertTrue(
      $isValidTimeboxMethod->invoke($this->service, $optin)
    );
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
  }
  /**
   * @test
   * @group library
   */
  public function isValidTimeboxShouldReturnTrueWhenUnlimitedLifetimeIsConfigured()
  {
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      0
    );
    
    $optin = new \Orm\Entity\OptIn;
    $optin->setTimestamp(new \DateTime);
    $optin->setMode(\Orm\Entity\OptIn::MODE_REGISTER);
    
    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    
    $isValidTimeboxMethod->setAccessible(true);
    $this->assertTrue(
      $isValidTimeboxMethod->invoke($this->service, $optin)
    );
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_REGISTER,
      $formerLifetime
    );
    
    $formerLifetime = OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      0
    );
    
    $optin = new \Orm\Entity\OptIn;
    $optin->setTimestamp(new \DateTime);
    $optin->setMode(\Orm\Entity\OptIn::MODE_PASSWORD);
    
    $isValidTimeboxMethod = new \ReflectionMethod(
      'Cms\Service\Optin', 'isValidTimebox'
    );
    $isValidTimeboxMethod->setAccessible(true);
    $this->assertTrue(
      $isValidTimeboxMethod->invoke($this->service, $optin)
    );
    OptinTestHelper::changeConfiguredLifetime(
      \Orm\Entity\OptIn::MODE_PASSWORD,
      $formerLifetime
    );
  }
}