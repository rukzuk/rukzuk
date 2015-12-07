<?php


namespace Render\APIs\APIv1;


use Render\APIs\APIv1\HeadAPI;
use Test\Render\AbstractAPITestCase;
use Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists;

class HeadAPITest extends AbstractAPITestCase
{
  /**
   * Checks if only the language part returns if full locale
   * code is given (e.g. en-US, pt-BR)
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getInterfaceLanguage_returnLanguagePartOfFullLocaleCode()
  {
    // ARRANGE
    $localCode = 'pt_BR'; // brazilian portuguese
    $expectedInterfaceLanguage = 'pt'; // Portuguese (language part)
    $renderContextMock = $this->createRenderContextMock();
    $renderContextMock->expects($this->atLeastOnce())
      ->method('getInterfaceLocaleCode')
      ->will($this->returnValue($localCode));
    $headAPI = new HeadAPI($renderContextMock);

    // ACT
    $actualInterfaceLanguage = $headAPI->getInterfaceLanguage();

    // ASSERT
    $this->assertEquals($expectedInterfaceLanguage, $actualInterfaceLanguage);
  }

  /**
   * Checks if the language part returns if only language
   * is given at locale (e.g. en, pt)
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getInterfaceLanguage_returnLanguageEvenIfOnlyLanguagePartGiven()
  {
    // ARRANGE
    $expectedInterfaceLanguage = 'es'; // spain
    $renderContextMock = $this->createRenderContextMock();
    $renderContextMock->expects($this->atLeastOnce())
      ->method('getInterfaceLocaleCode')
      ->will($this->returnValue($expectedInterfaceLanguage));
    $headAPI = new HeadAPI($renderContextMock);

    // ACT
    $actualInterfaceLanguage = $headAPI->getInterfaceLanguage();

    // ASSERT
    $this->assertEquals($expectedInterfaceLanguage, $actualInterfaceLanguage);
  }

  /**
   * Checks if the expected website settings will be return
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getWebsiteSettings_returnExpectedWebsiteSettings()
  {
    // ARRANGE
    $websiteSettingsId = 'myWebsiteSettingsId';
    $expectedWebsiteSettings = array('test' => 'HeadAPITest');
    $websiteInfoStorageMock = $this->getWebsiteInfoStorageMock();
    $websiteInfoStorageMock->expects($this->any())
      ->method('getWebsiteSettings')
      ->with($this->equalTo($websiteSettingsId))
      ->will($this->returnValue($expectedWebsiteSettings));
    $renderContextMock = $this->createRenderContextMock();
    $renderContextMock->expects($this->atLeastOnce())
      ->method('getWebsiteInfoStorage')
      ->will($this->returnValue($websiteInfoStorageMock));
    $headAPI = new HeadAPI($renderContextMock);

    // ACT
    $actualWebsiteSettings = $headAPI->getWebsiteSettings($websiteSettingsId);

    // ASSERT
    $this->assertEquals($expectedWebsiteSettings, $actualWebsiteSettings);
  }

  /**
   * Checks if the expected website settings will be return
   *
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\APIs\APIv1\WebsiteSettingsNotFound
   */
  public function test_getWebsiteSettings_throwExceptionIfWebsiteSettingsNotExists()
  {
    // ARRANGE
    $websiteSettingsId = 'notExistsWebsiteSettings';
    $websiteInfoStorageMock = $this->getWebsiteInfoStorageMock();
    $websiteInfoStorageMock->expects($this->any())
      ->method('getWebsiteSettings')
      ->with($this->equalTo($websiteSettingsId))
      ->will($this->throwException(new WebsiteSettingsDoesNotExists()));
    $renderContextMock = $this->createRenderContextMock();
    $renderContextMock->expects($this->atLeastOnce())
      ->method('getWebsiteInfoStorage')
      ->will($this->returnValue($websiteInfoStorageMock));
    $headAPI = new HeadAPI($renderContextMock);

    // ACT
    $headAPI->getWebsiteSettings($websiteSettingsId);
  }

  /**
   * @return \Render\RenderContext
   */
  protected function createRenderContextMock()
  {
    $renderContextMock = $this->getMockBuilder('\Render\RenderContext')
      ->disableOriginalConstructor()->getMock();
    $renderContextMock->expects($this->any())
      ->method('getNavigationInfoStorage')
      ->will($this->returnValue($this->getNavigationInfoStorageMock()));
    return $renderContextMock;
  }

  /**
   * @return \Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage
   */
  protected function getNavigationInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\NavigationInfoStorage\INavigationInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

  protected function getWebsiteInfoStorageMock()
  {
    return $this->getMockBuilder('\Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage')
      ->disableOriginalConstructor()->getMock();
  }

}
 