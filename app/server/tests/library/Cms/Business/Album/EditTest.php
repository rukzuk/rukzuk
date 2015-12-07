<?php
namespace Cms\Business\Album;

use Cms\Business\Album as AlbumBusiness,
    Orm\Data\Album as DataAlbum,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * EditTest
 *
 * @package      Test
 * @subpackage   Business
 */
class EditTest extends ServiceTestCase
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
    $this->websiteId = 'SITE-be6e702f-10ac-4e1e-951f-307e4b8764al-SITE';
    $this->albumId = 'ALBUM-be1ecf03-acc4-4fdb-add4-72ebb0878006-ALBUM';
  }

  /**
   * @test
   * @group library
   */
  public function businessEditShouldEditAlbumAsExpected()
  {
    $allAlbumsForWebsiteId = $this->business->getAllByWebsiteId($this->websiteId);
    $this->assertTrue(count($allAlbumsForWebsiteId) === 1);

    $this->assertInstanceOf('Cms\Data\Album', $allAlbumsForWebsiteId[0]);
    $this->assertSame(
      'business_test_album_name_0',
      $allAlbumsForWebsiteId[0]->getName()
    );
    $lastUpdateDateBeforeUpdate = $allAlbumsForWebsiteId[0]->getLastUpdate();
    
    $updateValues = array(
      'name' => 'business_test_album_name_0_altered'
    );
    $editedAlbum = $this->business->edit(
      $this->albumId,
      $this->websiteId,
      $updateValues
    );

    $this->assertSame($updateValues['name'], $editedAlbum->getName());
    $this->assertSame($this->websiteId, $editedAlbum->getWebsiteid());
    $this->assertSame($this->albumId, $editedAlbum->getId());
    
    $this->assertNotSame($lastUpdateDateBeforeUpdate, $editedAlbum->getLastUpdate());
    $maxUpdateDateFromEntry = time();
    $minUpdateDateFromEntry = $maxUpdateDateFromEntry - 3;
    $this->assertGreaterThanOrEqual($minUpdateDateFromEntry, $editedAlbum->getLastUpdate());
    $this->assertLessThanOrEqual($maxUpdateDateFromEntry, $editedAlbum->getLastUpdate());
    
    $allAlbumsForWebsiteId = $this->business->getAllByWebsiteId($this->websiteId);
    $this->assertTrue(count($allAlbumsForWebsiteId) === 1);
    $this->assertInstanceOf('Cms\Data\Album', $allAlbumsForWebsiteId[0]);
    $this->assertSame(
      'business_test_album_name_0_altered',
      $allAlbumsForWebsiteId[0]->getName()
    );
  }
}