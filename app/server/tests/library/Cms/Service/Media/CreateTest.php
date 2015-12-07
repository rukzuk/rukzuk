<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Orm\Data\Media as DataMedia,
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
   * @var Cms\Service\Media
   */
  protected $service;

  /**
   * @var string
   */
  protected $websiteId;

  protected function setUp()
  {
    parent::setUp();

    $this->service = new MediaService('Media');
    $this->websiteId = 'SITE-375a709d-0176-4137-98b5-9616174b431a-SITE';
  }

  /**
   * @test
   * @group library
   */
  public function success()
  {
    $createValues = array(
      'name' => 'service test media',
      'albumid' => 'ALBUM-d6569ffb-4005-48db-992e-b40ea135c0d8-ALBUM',
      'extension' => 'jpg',
      'size' => '10000',
      'type' => 'image'
    );
    $testMedia = $this->service->create($this->websiteId, $createValues);
    
    $this->assertSame($createValues['name'], $testMedia->getName());
    $this->assertSame($this->websiteId, $testMedia->getWebsiteId());
    $this->assertSame($createValues['albumid'], $testMedia->getAlbumId());
    $this->assertSame($createValues['extension'], $testMedia->getExtension());
    $this->assertSame($createValues['size'], $testMedia->getSize());
    $this->assertSame($createValues['type'], $testMedia->getType());
    
    // Timestamp der letzten Aenderung darf nicht aelter sein als ein paar Sekunden
    $this->assertNotNull($testMedia->getLastupdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $testMedia->getLastupdate());
    $this->assertGreaterThan($currentTime - 2, $testMedia->getLastupdate());
  }
}