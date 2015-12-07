<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Cms\Response,
    Test\Seitenbau\ServiceTestCase;
/**
 * VersionDecreaseTest
 *
 * @package      Cms
 * @subpackage   Service\Website
 */
class VersionDecreaseTest extends ServiceTestCase
{
  protected $service;

  protected $testEntry;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new WebsiteService('Website');

    $attributes = array(
      'name' => 'PHPUnit Test Website - Increase',
      'description' => 'website description',
      'navigation' => '[]',
      'publish' => '{}'
    );
    $this->testEntry = $this->service->create($attributes);
  }
  /**
   * @test
   * @group library
   */
  public function versionDecreaseShouldDecreaseNullVersionAsExpected()
  {
    $this->assertSame(0, $this->testEntry->getVersion());
    $decreasedVersion = $this->service->decreaseVersion($this->testEntry->getId());
    $this->assertSame(0, $decreasedVersion);
  }
  /**
   * @test
   * @group library
   */
  public function versionDecreaseShouldDecreaseNonNullVersionAsExpected()
  {
    $this->assertSame(0, $this->testEntry->getVersion());
    $increaseSteps = 4;
    $expectedVersionAfterDecrease = 3;
    
    for ($i = 1; $i <= $increaseSteps; ++$i) {
      $increasedVersion = $this->service->increaseVersion($this->testEntry->getId());
      $this->assertSame($i, $increasedVersion);
    }
    
    $decreasedVersion = $this->service->decreaseVersion($this->testEntry->getId());
    $this->assertSame($expectedVersionAfterDecrease, $decreasedVersion);
  }
}