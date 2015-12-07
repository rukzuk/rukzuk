<?php
namespace Cms\Service\Media;

use Cms\Service\Media as MediaService,
    Cms\Validator\UniqueId as UniqueIdValidator,
    Test\Seitenbau\ServiceTestCase as ServiceTestCase;
/**
 * EditTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class EditTest extends ServiceTestCase
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
  public function editShouldChangeTheItemNameAndLastModification()
  {
    $websiteId = 'SITE-te01e89c-22af-46cd-a651-fc42dc78fe50-SITE';
    $itemSource = $this->getItemByNameAndWebsiteId(
      'media-item_edit_source_name',
      $websiteId
    );

    $this->assertInstanceOf('Cms\Data\Media', $itemSource);

    $sourceId = $itemSource->getId();
    $sourceName = $itemSource->getName();
    $sourceLastupdate = $itemSource->getLastUpdate();
    $sourceLastModification = $itemSource->getLastmod();

    $editedName = $sourceName . '_edited';
    $result = $this->service->edit($sourceId, $websiteId, array('name' => $editedName));

    $itemEdited = $this->getItemByNameAndWebsiteId($editedName, $websiteId);

    $this->assertInstanceOf('Cms\Data\Media', $itemEdited);
    $this->assertNotSame($sourceName, $itemEdited->getName());
    $this->assertNotSame($sourceLastModification, $itemEdited->getLastmod());
    $this->assertSame($editedName, $itemEdited->getName());
    $this->assertSame($sourceId, $itemEdited->getId());

    $this->assertNotSame($sourceLastModification, $itemEdited->getLastUpdate());
    $currentTime = time();
    $this->assertLessThanOrEqual($currentTime, $itemEdited->getLastUpdate());
    $this->assertGreaterThanOrEqual($currentTime - 3, $itemEdited->getLastUpdate());
  }

  /**
   * @param string $name
   * @param string $websiteId
   * @return Cms\Response\Media
   */
  private function getItemByNameAndWebsiteId($name, $websiteId)
  {
    $medias = $this->service->getByWebsiteIdAndFilter($websiteId);

    foreach ($medias as $media)
    {
      if ($media->getName() === $name)
      {
        return $media;
      }
    }
  }

}