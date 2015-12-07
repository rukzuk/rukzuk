<?php


namespace Cms\Business\Cli;

use Seitenbau\FileSystem;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase as ServiceTestCase;
use Seitenbau\Registry as Registry;
use Cms\Business\Cli as CliBusiness;
use Seitenbau\FileSystem as FS;

class SendStatisticToAnalyticsTest extends ServiceTestCase
{
  protected $sqlFixtures = array('library_Cms_Business_SendStatisticToAnalyticsTest.json');
  protected $cfg_segmentio;
  protected $analyticsLogFile;
  protected $spaceHost;

  protected function setUp()
  {
    parent::setUp();

    $weburl = parse_url(Registry::getBaseUrl());
    $this->spaceHost = preg_replace('/\.$/', '', $weburl['host']);

    $this->cfg_segmentio = Registry::getConfig()->stats->segmentio->toArray();
    $this->analyticsLogFile = $this->cfg_segmentio['api_options']['filename'];

    FS::rmFile($this->analyticsLogFile);
  }

  /**
   * @test
   * @group library
   */
  public function test_sendStatisticToAnalyticsServices_Success()
  {
    // ARRANGE
    $trackingId = 'this_is_the_tracking_id';
    $this->enableAnalytics($trackingId);
    $expectedTraits = array(
      'diskUsage' => null,
      'usedWebsites' => 10,
      'publishingEnabledWebsites' => 9,
      'publishedWebsites' => 8,
      'publishedWebsitesInternalUrl' => array(
        'http://internal.cname.rz',
      ),
      'publishedWebsitesExternalUrl' => array(
        'external.host.rz',
        'http://external.url.rz',
        'http://external.url2.rz'
      ),
      'totalUsers' => 2,
      'usedModuleIds' => array(
        'rz_module_1',
        'rz_module_2',
        'rz_module_3',
      ),
    );
    $expectedTrack = array(
      'event' => 'Space Init',
      'properties' => array(
        'id' => 'unknown-id',
        'name' => 'unknown-name',
        'userLogin' => 'max@example.com',
        'space' => $this->spaceHost,
      ),
    );
    $expectedWebsites = $this->getTestWebsites();
    $sendToAnalytics = new SendStatisticToAnalytics($this->getUserBusinessMock(),
      $this->getActionLogBusiness(), $this->getWebsiteBusinessMock($expectedWebsites),
      $this->getBuilderBusinessMock(), $this->getTemplateBusinessMock($expectedWebsites));

    // ACT
    $sendToAnalytics->send();

    // ASSERT
    $stats = $this->getSentStats();
    $this->assertCount(2, $stats);
    $this->assertStatIdentify($stats[0], $trackingId, $expectedTraits);
    $this->assertStatTrack($stats[1], $trackingId, $expectedTrack);
  }

  protected function enableAnalytics($trackingId)
  {
    ConfigHelper::mergeIntoConfig(array(
      'stats' => array('segmentio' => array('enabled' => 1)),
      'owner' => array('trackingId' => $trackingId)
    ));
  }

  /**
   * @return array
   */
  protected function getSentStats()
  {
    $sentStats = array();
    $rawStats = file_get_contents($this->analyticsLogFile);
    foreach (explode("\n", $rawStats) as $row) {
      if (empty($row)) {
        continue;
      }
      $sentStats[] = json_decode($row, true);
    }
    return $sentStats;
  }

  protected function assertStatIdentify($stat, $trackingId, $expectedTraits)
  {
    $this->assertStatBase($stat, $trackingId, 'identify');

    $this->assertArrayHasKey('traits', $stat);
    foreach ($expectedTraits as $expectedKey => $expectedValue) {
      $this->assertArrayHasKey($expectedKey, $stat['traits']);
      if (!is_null($expectedValue)) {
        $this->assertEquals($expectedValue, $stat['traits'][$expectedKey]);
      }
    }
  }

  protected function assertStatTrack($stat, $trackingId, $expectedTrack)
  {
    $this->assertStatBase($stat, $trackingId, 'track');

    $this->assertArrayHasKey('event', $stat);
    $this->assertEquals($expectedTrack['event'], $stat['event']);

    $this->assertArrayHasKey('properties', $stat);
    foreach ($expectedTrack['properties'] as $expectedKey => $expectedValue) {
      $this->assertArrayHasKey($expectedKey, $stat['properties']);
      $this->assertEquals($expectedValue, $stat['properties'][$expectedKey]);
    }
  }

  protected function assertStatBase($stat, $trackingId, $action)
  {
    $this->assertInternalType('array', $stat);
    $this->assertArrayHasKey('userId', $stat);
    $this->assertEquals($trackingId, $stat['userId']);
    $this->assertArrayHasKey('context', $stat);
    $this->assertArrayHasKey('type', $stat);
    $this->assertEquals($action, $stat['type']);
  }

  /**
   * @return \Cms\Business\ActionLog
   */
  protected function getActionLogBusiness()
  {
    return new \Cms\Business\ActionLog('ActionLog');
  }

  /**
   * @return \Cms\Business\User
   */
  protected function getUserBusinessMock()
  {
    $stub = $this->getMockBuilder('\Cms\Business\User')
      ->disableOriginalConstructor()
      ->getMock();

    $user1 = new \Cms\Data\User();
    $user1->setId('user-1');
    $user2 = new \Cms\Data\User();
    $user2->setId('user-2');

    $stub->expects($this->any())
      ->method('getAll')
      ->will($this->returnValue(array($user1, $user2)));

    return $stub;
  }

  /**
   * @return \Cms\Business\Website
   */
  protected function getWebsiteBusinessMock($websites)
  {
    $stub = $this->getMockBuilder('\Cms\Business\Website')
      ->disableOriginalConstructor()
      ->getMock();

    $stub->expects($this->any())
      ->method('getAll')
      ->will($this->returnValue($websites));

    return $stub;
  }

  /**
   * @param \Cms\Data\Website[] $websites
   *
   * @return \Cms\Business\Template
   */
  protected function getTemplateBusinessMock($websites)
  {
    $stub = $this->getMockBuilder('\Cms\Business\Template')
      ->disableOriginalConstructor()
      ->setMethods(array('getIdsByWebsiteId', 'getUsedModuleIds'))
      ->getMock();

    $expectedWebsiteId = $websites[0]->getId();
    $expectedTemplates = array(
      $expectedWebsiteId => array(
        'TPL-00000000-0000-0000-0000-000000000001-TPL' => array('rz_module_1', 'rz_module_3'),
        'TPL-00000000-0000-0000-0000-000000000002-TPL' => array(),
        'TPL-00000000-0000-0000-0000-000000000003-TPL' => array('rz_module_2'),
      ),
    );

    $expectedTemplatesIds = array(
      $expectedWebsiteId => array_merge(array_keys($expectedTemplates[$expectedWebsiteId]),
        array('TPL-00000000-0000-0000-0000-00not0exists-TPL')),
    );

    $stub->expects($this->any())
      ->method('getIdsByWebsiteId')
      ->will($this->returnCallback(function ($websiteId) use ($expectedTemplatesIds) {
        if (isset($expectedTemplatesIds[$websiteId])) {
          return $expectedTemplatesIds[$websiteId];
        } else {
          return array();
        }
      }));

    $stub->expects($this->any())
      ->method('getUsedModuleIds')
      ->will($this->returnCallback(function ($websiteId, $id) use ($expectedTemplates) {
        if (!isset($expectedTemplates[$websiteId])) {
          return array();
        }
        if (isset($expectedTemplates[$websiteId][$id])) {
          return $expectedTemplates[$websiteId][$id];
        }
        throw new \Exception('TEST_EXCEPTION');
      }));

    return $stub;
  }

  /**
   * @return \Cms\Business\Builder
   */
  protected function getBuilderBusinessMock()
  {
    $stub = $this->getMockBuilder('\Cms\Business\Builder')
      ->disableOriginalConstructor()
      ->getMock();

    // Build failed
    $publishedStatusFailed = new \Cms\Data\PublisherStatus();
    $publishedStatusFailed->setStatus($publishedStatusFailed::STATUS_FAILED);
    $buildFailed = new \Cms\Data\Build();
    $buildFailed->setLastPublished($publishedStatusFailed);

    // Build finished
    $publishedStatusFinished = new \Cms\Data\PublisherStatus();
    $publishedStatusFinished->setStatus($publishedStatusFinished::STATUS_FINISHED);
    $buildFinished = new \Cms\Data\Build();
    $buildFinished->setLastPublished($publishedStatusFinished);

    $stub->expects($this->any())
      ->method('getWebsiteBuilds')
      ->will($this->returnValueMap(array(
        array('publishing_disabled', array()),
        array('publishing_failed', array($buildFailed)),
        array('internal_no_cname', array($buildFinished)),
        array('internal_empty_cname', array($buildFinished)),
        array('internal_with_cname', array($buildFinished)),
        array('external_no_url_no_host', array($buildFinished)),
        array('external_empty_url_empty_host', array($buildFinished)),
        array('external_with_host_empty_url', array($buildFinished)),
        array('external_empty_host_with_url', array($buildFinished)),
        array('external_with_host_with_url', array($buildFinished)),
      )));

    return $stub;
  }

  /**
   * @return \Cms\Data\Website[]
   */
  protected function getTestWebsites()
  {
    $websites = array();

    // publishing disabled
    $websites[] = $this->createWebsiteData('publishing_disabled', false, array());

    // publishing failed
    $websites[] = $this->createWebsiteData('publishing_failed', true, array());

    // internal no cname
    $websites[] = $this->createWebsiteData('internal_no_cname', true, array(
      'type' => 'internal'));

    // internal empty cname
    $websites[] = $this->createWebsiteData('internal_empty_cname', true, array(
      'type' => 'internal',
      'cname' => ''));

    // internal with cname
    $websites[] = $this->createWebsiteData('internal_with_cname', true, array(
      'type' => 'internal',
      'cname' => 'internal.cname.rz'));

    // external no url, no host
    $websites[] = $this->createWebsiteData('external_no_url_no_host', true, array(
      'type' => 'external'));

    // external empty url, empty host
    $websites[] = $this->createWebsiteData('external_empty_url_empty_host', true, array(
      'type' => 'external',
      'host' => '',
      'url' => ''));

    // external with host, empty url
    $websites[] = $this->createWebsiteData('external_with_host_empty_url', true, array(
      'type' => 'external',
      'host' => 'external.host.rz',
      'url' => ''));

    // external empty host, with url
    $websites[] = $this->createWebsiteData('external_empty_host_with_url', true, array(
      'type' => 'external',
      'host' => '',
      'url' => 'http://external.url.rz'));

    // external with host, with url
    $websites[] = $this->createWebsiteData('external_with_host_with_url', true, array(
      'type' => 'external',
      'host' => 'external.host2.rz',
      'url' => 'http://external.url2.rz'));

    return $websites;
  }

  /**
   * @param $id
   * @param $publishingEnabled
   * @param $publishData
   * @return \Cms\Data\Website
   */
  protected function createWebsiteData($id, $publishingEnabled, $publishData)
  {
    $website = new \Cms\Data\Website();
    $website->setId($id);
    $website->setName('this is the name of "'.$id.'"');
    $website->setPublishingEnabled($publishingEnabled);
    $website->setPublish(json_encode($publishData));
    return $website;
  }
}