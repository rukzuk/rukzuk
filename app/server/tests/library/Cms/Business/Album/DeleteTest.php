<?php
namespace Cms\Business\Album;

use Cms\Business\Album as AlbumBusiness,
    Orm\Data\Album as DataAlbum,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * DeleteTest
 *
 * @package      Test
 * @subpackage   Business
 */
class DeleteTest extends ServiceTestCase
{
  /**
   * @var Cms\Business\Album
   */
  protected $business;
  /**
   * @var string 
   */
  protected $websiteId;
  /**
   * @var string 
   */
  protected $albumId;
  
  protected function setUp()
  {
    parent::setUp();

    $this->business = new AlbumBusiness('Album');
    $this->websiteId = 'SITE-be6e702f-10ac-4e1e-951f-307d4b8d60al-SITE';
    $this->albumId = 'ALBUM-ce2ecf0d-acc4-4fdb-add4-72ebb0878008-ALBUM';
  }
  /**
   * @test
   * @group library
   */
  public function businessDeleteShouldDeleteAlbumAsExpected()
  {
    $allAlbumsForWebsiteId = $this->business->getAllByWebsiteId($this->websiteId);
    $this->assertTrue(count($allAlbumsForWebsiteId) === 1);
    
    $nonDeletables = $this->business->delete($this->albumId, $this->websiteId);
        
    $this->assertInternalType('array', $nonDeletables);
    $this->assertSame(0, count($nonDeletables));
    
    $allAlbumsForWebsiteId = $this->business->getAllByWebsiteId($this->websiteId);
    $this->assertTrue(count($allAlbumsForWebsiteId) === 0);
  }
}