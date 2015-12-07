<?php


namespace Cms\Business\Website;


use Test\Seitenbau\ServiceTestCase;
use \Cms\Data\Website as DataWebsite;

class CopyTest extends ServiceTestCase
{
  /**
   * @test
   * @group small
   * @group library
   */
  public function test_copy_success()
  {
    // ARRANGE
    $fromWebsiteId = 'old-website-id';
    $newName = 'new website name';
    $toWebsiteId = 'new-website-id';
    $expectedWebsite = new DataWebsite();
    $expectedWebsite->setId($toWebsiteId);
    $expectedWebsite->setName($newName);

    $websiteServiceMock = $this->getMockBuilder('\Cms\Service\Website')
      ->disableOriginalConstructor()->getMock();
    $websiteServiceMock->expects($this->once())->method('copy')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($newName))
      ->will($this->returnValue($expectedWebsite));

    $pageServiceMock = $this->getMockBuilder('\Cms\Service\Page')
      ->disableOriginalConstructor()->getMock();
    $pageServiceMock->expects($this->once())->method('copyPagesToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $templateServiceMock = $this->getMockBuilder('\Cms\Service\Template')
      ->disableOriginalConstructor()->getMock();
    $templateServiceMock->expects($this->once())->method('copyToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $snippetServiceMock = $this->getMockBuilder('\Cms\Service\TemplateSnippet')
      ->disableOriginalConstructor()->getMock();
    $snippetServiceMock->expects($this->once())->method('copyToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $moduleServiceMock = $this->getMockBuilder('\Cms\Service\Modul')
      ->disableOriginalConstructor()->getMock();
    $moduleServiceMock->expects($this->once())->method('copyToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $albumServiceMock = $this->getMockBuilder('\Cms\Service\Album')
      ->disableOriginalConstructor()->getMock();
    $albumServiceMock->expects($this->once())->method('copyAlbumsToNewWebsiteId')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $mediaServiceMock = $this->getMockBuilder('\Cms\Service\Media')
      ->disableOriginalConstructor()->getMock();
    $mediaServiceMock->expects($this->once())->method('copyMediaToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $websiteSettingsMock = $this->getMockBuilder('\Cms\Service\WebsiteSettings')
      ->disableOriginalConstructor()->getMock();
    $websiteSettingsMock->expects($this->once())->method('copyToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $packageMock = $this->getMockBuilder('\Cms\Service\Package')
      ->disableOriginalConstructor()->getMock();
    $packageMock->expects($this->once())->method('copyToNewWebsite')
      ->with($this->equalTo($fromWebsiteId), $this->equalTo($toWebsiteId));

    $websiteBusinessMock = $this->getMockBuilder('\Cms\Business\Website')
      ->disableOriginalConstructor()
      ->setMethods(array('getService'))
      ->getMock();
    $websiteBusinessMock->expects($this->any())->method('getService')
      ->will($this->returnValueMap(array(
        array('', $websiteServiceMock),
        array('Page', $pageServiceMock),
        array('Template', $templateServiceMock),
        array('TemplateSnippet', $snippetServiceMock),
        array('Modul', $moduleServiceMock),
        array('Album', $albumServiceMock),
        array('Media', $mediaServiceMock),
        array('WebsiteSettings', $websiteSettingsMock),
        array('Package', $packageMock),
      )));

    // ACT
    $actualWebsite = $websiteBusinessMock->copy($fromWebsiteId, $newName);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\Website', $actualWebsite);
    $this->assertEquals($expectedWebsite->getId(), $actualWebsite->getId());
    $this->assertEquals($expectedWebsite->getName(), $actualWebsite->getName());
  }
}
