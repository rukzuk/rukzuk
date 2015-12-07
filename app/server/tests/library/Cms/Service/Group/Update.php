<?php
namespace Cms\Service\Group;

use Cms\Service\Group as GroupService,
    Orm\Data\Group as DataGroup,
    Test\Seitenbau\ServiceTestCase;

/**
 * UpdateTest
 *
 * @package      Test
 * @subpackage   Service
 */

class UpdateTest extends ServiceTestCase
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
  public function checkUpdateDateOnReturnObject()
  {
    $groupId = 'GROUP-2d1fca5b-1bc5-4558-b684-4cd3322eb923-GROUP';
    
    $editValues = array(
      'name' => 'test edit last update'
    );
    $testGroup = $this->service->edit($groupId, $this->websiteId, $editValues);

    $this->assertInstanceOf('Cms\Data\Group', $testGroup);
    $this->assertSame($editValues['name'], $testGroup->getName());
    $this->assertSame($this->websiteId, $testGroup->getWebsiteId());
    $this->assertSame($groupId, $testGroup->getId());
    
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($testGroup->getLastupdate());
    $maxAlter = date('Y-m-d H:i:s', (time()-2));
    $this->assertGreaterThan($maxAlter, $testGroup->getLastupdate());
  }
}