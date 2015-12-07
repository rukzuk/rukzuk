<?php


namespace Render\IconHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;

class SimpleIconHelper implements IIconHelper
{
  /**
   * @var string
   */
  private $iconDirectory;

  /**
   * @var string
   */
  private $fallBackIconName;

  /**
   * @param string $iconDirectory
   * @param string $fallBackIconName
   */
  public function __construct($iconDirectory, $fallBackIconName)
  {
    $this->iconDirectory = $iconDirectory;
    $this->fallBackIconName = $fallBackIconName;
  }

  /**
   * Returns the filePath to the related icon
   *
   * @param string $mediaFilePath
   *
   * @return string
   */
  public function getIconFilePath($mediaFilePath)
  {
    $fileExtension = $this->getFileExtension($mediaFilePath);
    $iconFileName = $this->getIconForFileExtension($fileExtension);
    $iconFilePath = $this->createIconPath($iconFileName);
    if (!file_exists($iconFilePath)) {
      return $this->createIconPath($this->fallBackIconName);
    }
    return $iconFilePath;
  }

  /**
   * @param string $mediaFilePath
   *
   * @return string
   */
  protected function getFileExtension($mediaFilePath)
  {
    $fileName = basename($mediaFilePath);
    return substr(strrchr(strtolower($fileName), '.'), 1);
  }

  /**
   * @param $fileExtension
   *
   * @return string
   */
  protected function getIconForFileExtension($fileExtension)
  {
    return 'icon_' . $fileExtension . '.png';
  }

  /**
   * @param $iconFileName
   *
   * @return string
   */
  protected function createIconPath($iconFileName)
  {
    return $this->iconDirectory . DIRECTORY_SEPARATOR . $iconFileName;
  }
}
