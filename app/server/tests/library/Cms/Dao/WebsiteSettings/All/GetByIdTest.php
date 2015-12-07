<?php


namespace Cms\Dao\WebsiteSettings\Filesystem;

use Test\Cms\Dao\WebsiteSettings\AbstractDaoTestCase;
use Seitenbau\FileSystem as FS;
use Cms\Data\WebsiteSettings as WebsiteSettingsData;

/**
 * Class GetByIdTest
 *
 * @package Cms\Dao\WebsiteSettings\All
 *
 * @group websiteSettings
 */
class GetByIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getById_returnsMergedFormValues()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID';
    $websiteSettingsId = 'rz_shop';
    $baseDirectory = $this->getBaseDirectory();
    $source = $this->getWebsiteSettingsSource($websiteId, array(
      array($baseDirectory, $websiteSettingsId),
    ));
    $expectedFormValues = (object) array(
      'filesystem' => array('foo', 'bar'),
      'doctrine' => array('bar', 'foo'),
      'both_object' => (object) array('dao' => 'doctrine'),
      'both_array' => array('dao' => 'doctrine'),
      'both_string' => 'doctrine',
    );

    $filesystemData = new WebsiteSettingsData();
    $filesystemData->setWebsiteId($websiteId);
    $filesystemData->setId($websiteSettingsId);
    $filesystemData->setFormValues((object) array(
      'filesystem' => array('foo', 'bar'),
      'both_object' => (object) array('dao' => 'filesystem'),
      'both_array' => array('dao' => 'filesystem'),
      'both_string' => 'filesystem',
    ));
    $filesystemDaoMock = $this->getFilesystemDaoMock();
    $filesystemDaoMock->expects($this->once())
      ->method('getById')
      ->with($this->equalTo($source), $this->equalTo($websiteSettingsId))
      ->will($this->returnValue($filesystemData));

    $doctrineData = new WebsiteSettingsData();
    $doctrineData->setWebsiteId($websiteId);
    $doctrineData->setId($websiteSettingsId);
    $doctrineData->setFormValues((object) array(
      'doctrine' => array('bar', 'foo'),
      'both_object' => (object) array('dao' => 'doctrine'),
      'both_array' => array('dao' => 'doctrine'),
      'both_string' => 'doctrine',
    ));
    $doctrineDaoMock = $this->getDoctrineDaoMock();
    $doctrineDaoMock->expects($this->once())
      ->method('exists')
      ->with($this->equalTo($source), $this->equalTo($websiteSettingsId))
      ->will($this->returnValue(true));
    $doctrineDaoMock->expects($this->once())
      ->method('getById')
      ->with($this->equalTo($source), $this->equalTo($websiteSettingsId))
      ->will($this->returnValue($doctrineData));

    $dao = $this->getAllDao($filesystemDaoMock, $doctrineDaoMock);

    // ACT
    $actualWebsiteSettings = $dao->getById($source, $websiteSettingsId);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\WebsiteSettings', $actualWebsiteSettings);
    $this->assertEquals($expectedFormValues, $actualWebsiteSettings->getFormValues());
  }
}
 