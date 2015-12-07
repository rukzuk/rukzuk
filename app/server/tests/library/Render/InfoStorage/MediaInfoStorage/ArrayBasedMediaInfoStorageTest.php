<?php


namespace Render\InfoStorage\MediaInfoStorage;


use Render\IconHelper\SimpleIconHelper;

class ArrayBasedMediaInfoStorageTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists
   */
  public function test_itemDoesNotExists()
  {
    $urlHelperMock = $this->getMock('\Render\MediaUrlHelper\IMediaUrlHelper');
    $iconHelper = $this->getMock('Render\IconHelper\IIconHelper');
    $infoStorage = new ArrayBasedMediaInfoStorage(array(), $urlHelperMock,
      $iconHelper);
    $infoStorage->getItem('media-does-not-exists-media');
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_itemReturning()
  {
    // ARRANGE
    $item1 = $this->generateMediaInfoStorageItem(
      'MEDIA-4100188d-48f1-49ea-99a1-71b518810b1c-MEDIA',
      15, 1392029937);
    $item2 = $this->generateMediaInfoStorageItem(
      'MEDIA-0146295a-52c6-413e-9334-baf898ce120c-MEDIA',
      4096, 1392029938);
    $itemMap = array($item1['id'] => $item1, $item2['id'] => $item2);
    // ACT
    $urlHelperMock = $this->getMock('\Render\MediaUrlHelper\IMediaUrlHelper');
    $iconHelper = $this->getMock('Render\IconHelper\IIconHelper');
    $infoStorage = new ArrayBasedMediaInfoStorage($itemMap, $urlHelperMock,
      $iconHelper);
    $return2 = $infoStorage->getItem($item2['id']);
    $return1 = $infoStorage->getItem($item1['id']);
    // ASSERT
    $this->validateMediaInfoStorageItem($item1, $return1);
    $this->validateMediaInfoStorageItem($item2, $return2);
  }

  protected function validateMediaInfoStorageItem(array $itemArray, MediaInfoStorageItem $infoStorageItem)
  {
    $this->assertEquals($itemArray['id'], $infoStorageItem->getId());
    $this->assertEquals($itemArray['name'], $infoStorageItem->getName());
    $this->assertEquals($itemArray['size'], $infoStorageItem->getSize());
    $this->assertEquals($itemArray['filePath'], $infoStorageItem->getFilePath());
    $this->assertEquals($itemArray['lastModified'], $infoStorageItem->getLastModified());
  }

  protected function generateMediaInfoStorageItem($id, $size, $lastModified)
  {
    return array('id' => $id, 'name' => 'Name of ' . $id,
      'filePath' => '/file/path/of/' . $id, 'size' => $size,
      'lastModified' => $lastModified);
  }

}

