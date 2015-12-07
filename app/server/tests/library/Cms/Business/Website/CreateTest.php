<?php


namespace Cms\Business\Website;


use Cms\Business\Website as WebsiteBusiness;
use Test\Seitenbau\ServiceTestCase;


class CreateTest extends ServiceTestCase
{
  /**
   * @test
   * @group library
   */
  public function success()
  {
    // ARRANGE
    $attributes = array(
      'name' => 'PHPUnit Test Website - Create',
      'description' => 'website description',
      'navigation' => '[]',
      'publish' => '{}'
    );
    $moduleServiceMock = $this->getMock('\Cms\Service\Modul',
      array('createStorageForWebsite'), array('Modul'));
    $moduleServiceMock->expects($this->atLeastOnce())
      ->method('createStorageForWebsite');
    $websiteBusinessMock = $this->getMock('\Cms\Business\Website',
      array('getModuleService'), array('Website'));
    $websiteBusinessMock->expects($this->any())
      ->method('getModuleService')
      ->will($this->returnValue($moduleServiceMock));

    // ACT
    $websiteBusinessMock->create($attributes);
  }

}