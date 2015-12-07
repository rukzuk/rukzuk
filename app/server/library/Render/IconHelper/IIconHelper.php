<?php


namespace Render\IconHelper;

interface IIconHelper
{

  /**
   * Returns the filePath to the related icon
   *
   * @param string $mediaFilePath
   *
   * @return string
   */
  public function getIconFilePath($mediaFilePath);
}
