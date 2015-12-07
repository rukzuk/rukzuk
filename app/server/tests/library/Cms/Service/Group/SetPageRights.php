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

class SetPageRightsTest extends ServiceTestCase
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
    $groupId = 'GROUP-863dd940-6595-4b30-b7b2-9307b623e8cd-GROUP';
    $pageRights = array('allrights' => true);
    $testGroup = $this->service->setPageRights($groupId, $this->websiteId, $pageRights);

    $this->assertNotNull($testGroup->getLastupdate());
    $maxAlter = date('Y-m-d H:i:s', (time()-2));
    $this->assertGreaterThan($maxAlter, $testGroup->getLastupdate());
  }
}