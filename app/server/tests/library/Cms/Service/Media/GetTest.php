<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class GetTest extends ServiceTestCase
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
    $this->websiteId = 'SITE-ra10e89c-22af-46cd-a651-fc42dc78fe50-SITE';
  }

  /**
   * @test
   * @group library
   */
  public function getWithoutAFilterShouldActAsGetByWebsiteId()
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter($this->websiteId);
    
    $this->assertTrue(count($mediaItems) > 0);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }
  }
  /**
   * @test
   * @group library
   * @dataProvider  startAndLimitFilterProvider
   * @param array   $filter
   * @param integer $expectedItemCount
   */
  public function getShouldReturnExpectedMediaitemsCountsForStartAndLimitFilters(
    $filter, $expectedItemCount)
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter($this->websiteId, $filter);
    
    $this->assertTrue(count($mediaItems) === $expectedItemCount);

    foreach ($mediaItems as $mediaItem)
    {
      $this->assertInstanceOf('Cms\Data\Media', $mediaItem);
    }
  }
  /**
   * @test
   * @group library
   * @dataProvider  sortAndDirectionFilterProvider
   * @param array   $filter
   */
  public function getShouldReturnOrderedMediaItemsForSortAndDirectionFilters(
    $filter)
  {
    $mediaItems = $this->service->getByWebsiteIdAndFilter($this->websiteId, $filter);
    
    $this->assertInstanceOf('Cms\Data\Media', $mediaItems[0]);
    $this->assertTrue(count($mediaItems) > 0);

    if ($filter['sort'] === 'name')
    {
      if ($filter['direction'] === 'ASC')
      {
        $this->assertSame($mediaItems[0]->getName(), 'aaaaaaaa-media-filter');
        $this->assertSame($mediaItems[1]->getName(), 'bbbbbbbb-media-filter');
      }
      if ($filter['direction'] === 'DESC')
      {
        $this->assertSame($mediaItems[0]->getName(), 'zzzzzzzz-media-filter');
        $this->assertSame($mediaItems[1]->getName(), 'yyyyyyyy-media-filter');
      }
    }
    if ($filter['sort'] === 'extension')
    {
      if ($filter['direction'] === 'ASC')
      {
        $this->assertSame($mediaItems[0]->getExtension(), 'aaaaaa-ext');
      }
      if ($filter['direction'] === 'DESC')
      {
        $this->assertSame($mediaItems[0]->getExtension(), 'zzzzzz-ext');
      }
    }
  }

  /**
   * @test
   * @group library
   * @dataProvider nonAllowedColumnsProvider
   * @param string $dataClassName
   */
  public function getShouldThrowNoExceptionForNonAllowedColumns($column)
  {
    $this->service->getByWebsiteIdAndFilter(
      $this->websiteId, array('sort' => $column)
    );
  }

  /**
   * @return array
   */
  public function sortAndDirectionFilterProvider()
  {
    return array(
      array(
        array('sort' => 'name', 'direction' => 'ASC'), // filter
      ),
      array(
        array('sort' => 'name', 'direction' => 'DESC'), // filter
      ),
      array(
        array('sort' => 'extension', 'direction' => 'ASC'), // filter
      ),
      array(
        array('sort' => 'extension', 'direction' => 'DESC'), // filter
      ),
    );
  }
  /**
   * @return array
   */
  public function startAndLimitFilterProvider()
  {
    return array(
      array(
        array('start' => 0, 'limit' => 10), // filter
        10, // Expected MediaItem Count
      ),
      array(
        array('start' => 0, 'limit' => 7),
        7,
      ),
      array(
        array('start' => 2, 'limit' => 4),
        4,
      ),
    );
  }
  /**
   * @return array
   */
  public function nonAllowedColumnsProvider()
  {
    return array(
      array('test_sort_not_existent'),
      array('db_column_not_existent'),
      array(null),
      array(array()),
    );
  }
}