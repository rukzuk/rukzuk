<?php
namespace Cms\Business\Album;

use Cms\Business\Album as AlbumBusiness,
    Orm\Data\Album as DataAlbum,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * GetAllTest
 *
 * @package      Test
 * @subpackage   Business
 */
class GetAllTest extends ServiceTestCase
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
    $this->websiteId = 'SITE-be6e702g-10ac-4e1e-951f-307e4b8760al-SITE';
  }
  /**
   * @test
   * @group library
   */
  public function businessGetAllByWebsiteIdShouldReturnExpectedAlbums()
  {
    $allAlbumsForWebsiteId = $this->business->getAllByWebsiteId($this->websiteId);
    $this->assertTrue(count($allAlbumsForWebsiteId) === 4);
    foreach ($allAlbumsForWebsiteId as $albumsOfWebsiteId) {
      $this->assertInstanceOf('Cms\Data\Album', $albumsOfWebsiteId);
      $this->assertSame($this->websiteId, $albumsOfWebsiteId->getWebsiteId());
      $this->assertTrue(
        $this->validateUniqueId(new DataAlbum, $albumsOfWebsiteId->getId())
      );
      $this->assertNotEmpty($albumsOfWebsiteId->getName());
    }
  }
}