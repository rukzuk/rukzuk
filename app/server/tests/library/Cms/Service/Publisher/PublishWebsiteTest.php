<?php
namespace Cms\Service\Publisher;

use Cms\Service\Publisher as PublisherService,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\PublisherServiceTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * PublishWebsiteTest
 *
 * @package      Cms
 * @subpackage   Service\Publisher
 */
class PublishWebsiteTest extends PublisherServiceTestCase
{
  const BACKUP_CONFIG = true;

  private $service;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new PublisherService;
  }

  protected function tearDown()
  {
    $this->removeWebsiteBuilds();
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 602
   */
  public function publishWebsiteShouldThrowExpectedExceptionWhenWebsiteDoesNotExist()
  {
    $nonExistingWebsiteId = 'SITE-no000000-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $buildId = 'v1-1312384635';
    $this->service->publishWebsite($nonExistingWebsiteId, $buildId);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 622
   */
  public function publishWebsiteShouldThrowExpectedExceptionWhenWebsiteBuildDoesNotExist()
  {
    $websiteId = 'SITE-bw10fg14-3bbe-4301-ae51-f58464f1708e-SITE';
    $buildId = 'v2-1312384635';
    $this->service->publishWebsite($websiteId, $buildId);
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 624
   */
  public function publishWebsiteShouldThrowExpectedExceptionIfWebsitePublishingIsDisabled()
  {
    $websiteId = 'SITE-website0-publ-ishi-ng-d-isabled00001e-SITE';
    $buildId = 'v2-1312384635';
    $this->service->publishWebsite($websiteId, $buildId);
  }
}