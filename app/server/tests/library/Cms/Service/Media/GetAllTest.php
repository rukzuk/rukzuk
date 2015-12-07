<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetAllTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */
class GetAllTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media
   */
  private $service;
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var string
   */
  private $albumId;

  public function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
    $this->websiteId = 'SITE-ra10e89c-22af-46sf-a651-fc42dc78f7ga-SITE';
    $this->albumId = 'ALBUM-ra10e89c-22af-46sf-a651-fc42dc78f7ga-ALBUM';
  }
  /**
   * @test
   * @group library
   */
  public function getAllWithSortAndDirectionShouldReturnAllMediasSortedAndOrdered()
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter(
      $this->websiteId,
      array(
        'albumid' => $this->albumId, 
        'sort' => 'type', 
        'direction' => 'DESC'
      )
    );
    
    $this->assertTrue(count($mediaItems) == 5);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }
    
    $this->assertSame('misc', $mediaItems[0]->getType());
  }
  /**
   * @test
   * @group library
   */
  public function getAllWithTypeAndLimitFilterShouldReturnTypeMatchesAndApplyLimit()
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter(
      $this->websiteId,
      array(
        'type' => 'image', 
        'limit' => 3,
        'albumid' => $this->albumId)
    );
    
    $this->assertTrue(count($mediaItems) == 3);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }
  }
}