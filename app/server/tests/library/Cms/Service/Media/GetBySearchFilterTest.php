<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * GetBySearchFilterTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */
class GetBySearchFilterTest extends ServiceTestCase
{  
  /**
   * @var Cms\Service\Media
   */
  private $service;
  /**
   * @var string
   */
  private $websiteId;

  public function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
    $this->websiteId = 'SITE-ra10e89c-22af-46sf-a651-fc42dc78fe50-SITE';
  }
  /**
   * @test
   * @group library
   */
  public function getWithSearchFilterShouldOnlyReturnMatchingMedias()
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter(
      $this->websiteId,
      array('search' => 'Test_Data_Search_Filter')
    );

    $this->assertTrue(count($mediaItems) == 2);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }
  }

  /**
   * @test
   * @group library
   */
  public function getWithSearchAndLimitFilterShouldReturnMatchesAndApplyLimit()
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter(
      $this->websiteId,
      array('search' => 'Test_Data_Search_Filter', 'limit'  => 1)
    );
    
    $this->assertTrue(count($mediaItems) == 1);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }    
  }
}