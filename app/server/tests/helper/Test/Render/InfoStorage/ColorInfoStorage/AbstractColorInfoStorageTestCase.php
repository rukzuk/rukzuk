<?php


namespace Test\Render\InfoStorage\ColorInfoStorage;


use Test\Render\InfoStorage\AbstractInfoStorageTestCase;

/**
 * Class AbstractColorInfoStorageTestCase
 *
 * @package Test\Render\InfoStorage\ColorInfoStorage
 */
abstract class AbstractColorInfoStorageTestCase extends AbstractInfoStorageTestCase
{
  /**
   * @param array $colorScheme
   *
   * @return \Render\InfoStorage\NavigationInfoStorage\IColorInfoStorage
   */
  abstract protected function getColorInfoStorage(array $colorScheme);

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @dataProvider provider_test_getColorReturnsColorAsExpected
   */
  public function test_getColorReturnsColorAsExpected($colorScheme, $colorId, $expectedColor)
  {
    // ARRANGE
    $infoStorage = $this->getColorInfoStorage($colorScheme);

    // ACT
    $actualColor = $infoStorage->getColor($colorId);

    // ASSERT
    $this->assertInternalType('string', $actualColor);
    $this->assertEquals($expectedColor, $actualColor);
  }

  /**
   * @return array
   */
  public function provider_test_getColorReturnsColorAsExpected()
  {
    $colorScheme = $this->getColorScheme();
    return array(
      array($colorScheme, 'COLOR-00000000-0000-0000-0000-000000000001--000000000000--COLOR', 'rgba(255,255,255,1)'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0000-000000000002--000000000000--COLOR', 'rgba(255,255,255,0.75)'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0001-000000000001--000000000000--COLOR', 'COLOR-00000000-0000-0000-0001-000000000001--000000000000--COLOR'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0001-000000000002--1ffffffff000--COLOR', 'rgba(255,255,255,1.00)'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0001-000000000002--1aabbcc55000--COLOR', 'rgba(170,187,204,0.85)'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0001-000000000002--1bbccaa65000--COLOR', 'rgba(187,204,170,1.00)'),
      array($colorScheme, 'COLOR-00000000-0000-0000-0001-000000000002--1bbccaaff000--COLOR', 'rgba(187,204,170,1.00)'),
      array($colorScheme, 'NONE-COLOR-ID-COLOR-NONE', 'NONE-COLOR-ID-COLOR-NONE'),
      array($colorScheme, 'COLOR-UNVALID-COLOR', 'COLOR-UNVALID-COLOR'),
    );
  }

  /**
   * @return array
   */
  protected function getColorScheme()
  {
    return array (
      'COLOR-00000000-0000-0000-0000-000000000001--000000000000--COLOR' =>
        array (
          'id' => 'COLOR-00000000-0000-0000-0000-000000000001--000000000000--COLOR',
          'value' => 'rgba(255,255,255,1)',
          'name' => 'weiß',
        ),
      'COLOR-00000000-0000-0000-0000-000000000002--000000000000--COLOR' =>
        array (
          'id' => 'COLOR-00000000-0000-0000-0000-000000000002--000000000000--COLOR',
          'value' => 'rgba(255,255,255,0.75)',
          'name' => 'weiß 75%',
        ),
   );
  }
}