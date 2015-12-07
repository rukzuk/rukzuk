<?php
namespace Cms\Service\Album;

use Cms\Service\Album as AlbumService,
    Orm\Data\Album as DataAlbum,
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
   * @var Cms\Service\Album
   */
  protected $service;
  /**
   * @var string 
   */
  protected $websiteId;
  
  protected function setUp()
  {
    parent::setUp();

    $this->service = new AlbumService('Album');
    $this->websiteId = 'SITE-se6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
  }
  /**
   * @test
   * @group library
   */
  public function serviceShouldCreateAlbumShouldCreateAlbumAsExpected()
  {
    $createValues = array(
      'name' => 'service_test_album_0'
    );
    $testAlbum = $this->service->create($this->websiteId, $createValues);
        
    $this->assertSame($createValues['name'], $testAlbum->getName());
    $this->assertSame($this->websiteId, $testAlbum->getWebsiteid());
    $this->assertTrue($this->validateUniqueId(new DataAlbum(), $testAlbum->getId()));
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($testAlbum->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $testAlbum->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $testAlbum->getLastupdate());
  }
}