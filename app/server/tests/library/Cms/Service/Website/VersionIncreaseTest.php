<?php
namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService,
    Cms\Response,
    Test\Seitenbau\ServiceTestCase;
/**
 * VersionIncreaseTest
 *
 * @package      Cms
 * @subpackage   Service\Website
 */
class VersionIncreaseTest extends ServiceTestCase
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
  public function versionIncreaseShouldIncreaseVersionAsExpected()
  {
    $this->assertSame(0, $this->testEntry->getVersion());
    $increasedVersion = $this->service->increaseVersion($this->testEntry->getId());
    $this->assertSame(1, $increasedVersion);
  }
}