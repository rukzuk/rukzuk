<?php
namespace Cms\Business\Album;

use Cms\Business\Album as AlbumBusiness,
    Orm\Data\Album as DataAlbum,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * CreateTest
 *
 * @package      Test
 * @subpackage   Business
 */
class CreateTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Album
   */
  protected $business;
  /**
   * @var string 
   */
  protected $websiteId;
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new AlbumBusiness('Album');
    $this->websiteId = 'SITE-be6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
  }  
  /**
   * @test
   * @group library
   */
  public function businessShouldCreateAlbumAsExpected()
  {
    $createValues = array(
      'name' => 'business_test_album_0'
    );
    $testAlbum = $this->business->create($this->websiteId, $createValues);
        
    $this->assertSame($createValues['name'], $testAlbum->getName());
    $this->assertSame($this->websiteId, $testAlbum->getWebsiteid());
    $this->assertTrue($this->validateUniqueId(new DataAlbum(), $testAlbum->getId()));
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($testAlbum->getLastupdate());
    $maxAlter = time()-2;
    $this->assertGreaterThan($maxAlter, $testAlbum->getLastupdate());
  }
}