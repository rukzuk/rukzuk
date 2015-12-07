<?php
namespace Cms\Data;

/**
 * Index
 *
 * @package      Cms
 * @subpackage   Data
 */
class Index
{
  /**
   * @var boolean
   */
  private $enabled;
  /**
   * @var string
   */
  private $localeIndexDirectory;
  /**
   * @var string
   */
  private $buildIndexDirectoryName;
  
  /**
   * @param boolean $enabled
   */
  public function setEnabled($enabled)
  {
    $this->enabled = $enabled;
    return $this;
  }
  /**
   * @return string
   */
  public function isEnabled()
  {
    return $this->enabled;
  }
  /**
   * @param string $directory
   */
  public function setLocalIndexDirectory($directory)
  {
    $this->localeIndexDirectory = $directory;
    return $this;
  }
  /**
   * @return string
   */
  public function getLocalIndexDirectory()
  {
    return $this->localeIndexDirectory;
  }
  /**
   * @param string $name
   */
  public function setBuildIndexDirectoryName($name)
  {
    $this->buildIndexDirectoryName = $name;
    return $this;
  }
  /**
   * @return string
   */
  public function getBuildIndexDirectoryName()
  {
    return $this->buildIndexDirectoryName;
  }
}
