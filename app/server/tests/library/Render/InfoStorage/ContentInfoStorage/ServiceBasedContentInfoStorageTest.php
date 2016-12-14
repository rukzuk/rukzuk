<?php


namespace Render\InfoStorage\ContentInfoStorage;


use Cms\Render\InfoStorage\ContentInfoStorage\ServiceBasedContentInfoStorage;

class ServiceBasedContentInfoStorageTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @expectedException PHPUnit_Framework_Error
   */
  public function test_constructor_denies_null()
  {
    new ServiceBasedContentInfoStorage(null, null);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider templateContentDataProvider
   */
  public function test_getTemplateContent($siteId, $templateId, $rawTemplateContent, $expectedTemplateContent)
  {
    // Prepare template Data Mock
    $templateMock = $this->getMock('\Cms\Data\Template');
    $templateMock->expects($this->once())
      ->method('getContent')
      ->will($this->returnValue($rawTemplateContent));
    // Prepare template Service Mock
    $serviceMock = $this->getMock('Cms\Service\Template', array(), array('Template'));
    $serviceMock->expects($this->once())
      ->method('getById')
      ->with($this->equalTo($templateId), $this->equalTo($siteId))
      ->will($this->returnValue($templateMock));
    // ACK
    $infoStorage = new ServiceBasedContentInfoStorage($siteId, $serviceMock);
    $actualCustomData = $infoStorage->getTemplateContent($templateId);
    // ASSERT
    $this->assertEquals($expectedTemplateContent, $actualCustomData, "Wrong template content returned");
  }

  /**
   * @return array
   */
  public function templateContentDataProvider()
  {
    $rawTemplateInfos = array(
      'TPL-1-TPL' => json_encode(array(array('foo' => 'bar'))),
      'TPL-2-TPL' => json_encode(array(array('bar' => 'foo')))
    );

    return array(
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'TPL-1-TPL',
        $rawTemplateInfos['TPL-1-TPL'],
        array('foo' => 'bar'),
      ),
      array(
        'SITE-280e80ff-ee1d-4716-9a96-d312dd2b88d8-SITE',
        'TPL-2-TPL',
        $rawTemplateInfos['TPL-2-TPL'],
        array('bar' => 'foo'),
      )
    );
  }
}
