<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * GetMultipleByIdsTest
 *
 * @package      Test
 * @subpackage   Cms\Service\Media
 */

class GetMultipleByIdsTest extends ServiceTestCase
{
  /**
   * @var Cms\Service\Media
   */
  private $service;

  public function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $websiteId = 'SITE-779ca2e0-7948-4bd6-958f-8a92e287fe22-SITE';
    $existsMediaIds = array(
        'MDB-e2611218-3590-4cdf-b7bc-4d59ed4c88aa-MDB', 
        'MDB-dca4f746-c420-407f-b145-7de175d2bb09-MDB'
    );
    $nonExistsMediaIds = array(
        'MDB-366869ef-6e4a-4646-b44c-5853a6cc994f-MDB', 
        'MDB-0733a4c1-1bef-4cc5-9ab3-67103e161984-MDB'
    );
    $mediaIds = array_merge($existsMediaIds, $nonExistsMediaIds);
    
    $medias = $this->service->getMultipleByIds($mediaIds, $websiteId);
        
    $this->assertInternalType('array', $medias);
    $this->assertSame(count($existsMediaIds), count($medias));
    
    $resultMediaIds = array();
    foreach ($medias as $media)
    {
      $this->assertInstanceOf('Cms\Data\Media', $media);
      $this->assertSame($websiteId, $media->getWebsiteId());
      $resultMediaIds[] = $media->getId();
    }
    sort($existsMediaIds);
    sort($resultMediaIds);
    $this->assertSame($existsMediaIds, $resultMediaIds);
  }
}