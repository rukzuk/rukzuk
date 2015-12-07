<?php

namespace Cms\Creator\Adapter\DynamicCreator;


/**
 * Class PreparePageResultTest
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class PreparePageResultTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_toArrayFromArray()
  {
    // ARRANGE
    $input = array(
      'websiteId' => 'SITE-ID',
      'id' => 'Page-ID',
      'meta' => array('meta'),
      'global' => array('global'),
      'content' => array('content'),
      'legacy' => false,
      'moduleIds' => array('moduleId'),
      'mediaIds' => array('mediaId'),
      'albumIds' => array('albumId'),
      'cssCache' => 'cssCode',
      'mediaUrlCalls' => array('mediaUrlCall'),
      'htmlCache' => 'htmlCode',
      'pageAttributes' => array('pageAttributes'),
    );

    $ppr = new PreparePageResult($input);

    // ACT
    $output = $ppr->toArray();

    // ASSERT
    $this->assertEquals($input, $output);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_toArrayFromArrayUnknownValue()
  {
    // ARRANGE
    $input = array(
      'websiteId' => 'SITE-ID',
      'id' => 'Page-ID',
      'meta' => array('meta'),
      'global' => array('global'),
      'content' => array('content'),
      'legacy' => false,
      'moduleIds' => array('moduleId'),
      'mediaIds' => array('mediaId'),
      'albumIds' => array('albumId'),
      'cssCache' => 'cssCode',
      'mediaUrlCalls' => array('mediaUrlCall'),
      'htmlCache' => 'htmlCode',
      'pageAttributes' => array('pageAttributes'),
      'fail' => 'someContent',
    );

    $ppr = new PreparePageResult($input);

    // ACT
    $output = $ppr->toArray();

    // ASSERT
    $this->assertNotEquals($input, $output);
  }

  /**
   * @test
   * @group rendering
   * @group creator
   * @group small
   * @group dev
   */
  public function test_toArrayFromArrayWebsiteIdMissing()
  {
    // ARRANGE
    $input = array(
      // REMOVED: 'websiteId' => 'SITE-ID',
      'id' => 'Page-ID',
      'meta' => array('meta'),
      'global' => array('global'),
      'content' => array('content'),
      'legacy' => false,
      'moduleIds' => array('moduleId'),
      'mediaIds' => array('mediaId'),
      'albumIds' => array('albumId'),
      'cssCache' => 'cssCode',
      'mediaUrlCalls' => array('mediaUrlCall'),
      'htmlCache' => 'htmlCode',
      'pageAttributes' => array('pageAttributes'),
    );

    $ppr = new PreparePageResult($input);

    // ACT
    $output = $ppr->toArray();

    // ASSERT
    $this->assertNotEquals($input, $output);
  }


}
