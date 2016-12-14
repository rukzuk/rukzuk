<?php


namespace Render\InfoStorage\ContentInfoStorage;


class ArrayBasedContentInfoStorageTest extends \PHPUnit_Framework_TestCase
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
    $templates = null;
    new ArrayBasedContentInfoStorage($templates);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider templateContentDataProvider
   */
  public function test_getTemplateContent($templateId, $templates, $expectedTemplateContent)
  {
    // ACK
    $infoStorage = new ArrayBasedContentInfoStorage($templates);
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
      'TPL-1-TPL' => array('content' => array('foo' => 'bar')),
      'TPL-2-TPL' => array('content' => array('bar' => 'foo'))
    );

    return array(
      array(
        'TPL-1-TPL',
        $rawTemplateInfos,
        array('foo' => 'bar'),
      ),
      array(
        'TPL-2-TPL',
        $rawTemplateInfos,
        array('bar' => 'foo'),
      )
    );
  }
}
