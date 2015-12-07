<?php
namespace Cms\Service\Group;

use Cms\Service\Group as GroupService,
    Orm\Data\Group as DataGroup,
    Test\Seitenbau\ServiceTestCase;

/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Service
 */

class CreateTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Group
   */
  protected $service;

  /**
   * @var string
   */
  protected $websiteId;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new GroupService('Group');
    $this->websiteId = 'SITE-caa8bc3a-2412-4c94-b35c-010ef9fb052c-SITE';
  }

  /**
   * @test
   * @group library
   */
  public function createGroupAsExpected()
  {
    $createValues = array(
      'name' => 'service_test_group_0'
    );
    $testGroup = $this->service->create($this->websiteId, $createValues);

    $this->assertInstanceOf('Cms\Data\Group', $testGroup);
    $this->assertSame($createValues['name'], $testGroup->getName());
    $this->assertSame($this->websiteId, $testGroup->getWebsiteId());
    $this->assertTrue($this->validateUniqueId(new DataGroup(), $testGroup->getId()));
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($testGroup->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $testGroup->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $testGroup->getLastupdate());
  }
}