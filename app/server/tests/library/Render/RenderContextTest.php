<?php


namespace Render;

use Test\Render\AbstractRenderTestCase;

class RenderContextTest extends AbstractRenderTestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getResolutions()
  {
    // ARRANGE
    $expectedResolution = array(
      'enabled' => true,
      'data' => array(
        'ID_WIDTH_LARGE'  => array('width' => 860, 'name' => 'Width large'),
        'ID_WIDTH_MEDIUM' => array('width' => 480, 'name' => 'Width medium'),
        'ID_WIDTH_SMALL'  => array('width' => 320, 'name' => 'Width small'),
    ));

    $renderContext = $this->createRenderContext(array(
      'resolutions' => $expectedResolution,
    ));

    // ACT
    $actualResolution = $renderContext->getResolutions();

    // ASSERT
    $this->assertEquals($expectedResolution, $actualResolution);
    $this->assertArrayKeyOrderEquals($expectedResolution['data'], $actualResolution['data']);

  }
}
 