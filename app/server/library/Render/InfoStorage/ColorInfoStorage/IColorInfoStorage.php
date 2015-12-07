<?php


namespace Render\InfoStorage\ColorInfoStorage;

interface IColorInfoStorage
{

  /**
   * @param string $colorId
   *
   * @return string
   */
  public function getColor($colorId);

  /**
   * All color ids known by this info storage
   * @return string[]
   */
  public function getColorIds();
}
