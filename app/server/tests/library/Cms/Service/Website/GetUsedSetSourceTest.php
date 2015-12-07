<?php
namespace Cms\Service\Website;


use Cms\Dao\Base\SourceItem;
use Test\Seitenbau\ServiceTestCase;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Cms\Service\Website as WebsiteService;

class GetUsedSetSourceTest extends ServiceTestCase
{
  /**
   * @var array
   */
  protected $sqlFixtures = array('library_Cms_Service_Website_GetUsedSetSourceTest.json');


  protected function setUp()
  {
    parent::setUp();

    $this->websiteId = 'SITE-website0-getU-sedS-etSo-urce00000001-SITE';
  }

  /**
   * @test
   * @group library
   * @group small
   * @group dev
   */
  public function test_getUsedSetSource_success()
  {
    // ARRANGE
    $this->enableGlobalSets();
    $service = new WebsiteService('Website');
    $expectedSourceItem = new SourceItem('rukzuk_test',
      FS::joinPath(Registry::getConfig()->test->directory, '_sets', 'rukzuk_test'),
      '/URL/TO/SETS/rukzuk_test', SourceItem::SOURCE_REPOSITORY, true, false);

    // ACT
    $usedSetSource = $service->getUsedSetSource($this->websiteId);
    $actualSourceItems = $usedSetSource->getSources();

    // ASSERT
    $this->assertInstanceOf('\Cms\Dao\Website\GlobalSetSource', $usedSetSource);
    $this->assertInternalType('array', $actualSourceItems);
    $this->assertCount(1, $actualSourceItems);
    $this->assertEquals($expectedSourceItem, $actualSourceItems[0]);
  }

  /**
   * @test
   * @group library
   * @group small
   * @group dev
   */
  public function test_getUsedSetSource_returnEmptySourceIfGlobalSetsDisabled()
  {
    // ARRANGE
    $this->disableGlobalSets();
    $service = new WebsiteService('Website');

    // ACT
    $usedSetSource = $service->getUsedSetSource($this->websiteId);
    $actualSourceItems = $usedSetSource->getSources();

      // ASSERT
    $this->assertInstanceOf('\Cms\Dao\Website\GlobalSetSource', $usedSetSource);
    $this->assertInternalType('array', $actualSourceItems);
    $this->assertCount(0, $actualSourceItems);
  }
}
 