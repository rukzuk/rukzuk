<?php


namespace Render\InfoStorage\ColorInfoStorage;


use Test\Render\InfoStorage\ColorInfoStorage\AbstractColorInfoStorageTestCase;

/**
 * Test Class for ArrayBasedColorInfoStorage
 *
 * @package Render\InfoStorage\ColorInfoStorage
 */
class ArrayBasedColorInfoStorageTest extends AbstractColorInfoStorageTestCase
{
  /**
   * @param array $colorScheme
   *
   * @return \Render\InfoStorage\NavigationInfoStorage\IColorInfoStorage
   */
  protected function getColorInfoStorage(array $colorScheme)
  {
    return new ArrayBasedColorInfoStorage($colorScheme);
  }
}
 