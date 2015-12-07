<?php
namespace Cms\Service\Album;

use Cms\Service\Album as Service,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;

/**
 * Tests fuer GetById Funktionalitaet Cms\Service\Album
 *
 * @package      Cms
 * @subpackage   Service\Page
 */

class GetByIdTest extends ServiceTestCase
{
  protected $service;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new Service('Album');
  }

  /**
   * @test
   * @group library
   */
  public function checkAttributes()
  {
    $websiteId = 'SITE-b9e6dcb1-b24b-47d6-8a8f-74e8d5ece16f-SITE';
    $albumId = 'ALBUM-53fb2e87-e2c5-4c30-a869-7a87dac8ecc4-ALBUM';
    
    $album = $this->service->getById($albumId, $websiteId);

    // Generelle Pruefung auf Data Objekt mit entsprechenden Attributen
    $this->assertInstanceOf('Cms\Data\Album', $album);
    $this->assertObjectHasAttribute('id', $album);
    $this->assertObjectHasAttribute('websiteid', $album);
    $this->assertObjectHasAttribute('name', $album);
    $this->assertObjectHasAttribute('lastUpdate', $album);
    
    // Werte pruefen
    $this->assertSame($websiteId, $album->getWebsiteId());
    $this->assertSame($albumId, $album->getId());
    
    // Album mit gleicher ID existiert zu einer anderen Website
    // Pruefung, ob richtige Website-Id beim Auslesen beachtet wird
    $websiteId = 'SITE-12dd3ad6-54be-4e14-a21c-8791b57081c6-SITE';
    
    $album = $this->service->getById($albumId, $websiteId);
    
    $this->assertInstanceOf('Cms\Data\Album', $album);
    $this->assertSame($websiteId, $album->getWebsiteId());
    $this->assertSame($albumId, $album->getId());
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   */
  public function idNotFound()
  {
    $id = 'TEST-123';
    $websiteId = 'SITE-b9e6dcb1-b24b-47d6-8a8f-74e8d5ece16f-SITE';
    
    $result = $this->service->getById($id, $websiteId);
  }
}