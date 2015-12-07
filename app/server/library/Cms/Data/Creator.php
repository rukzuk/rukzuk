<?php
namespace Cms\Data;

/**
 * Creator
 *
 * @package      Cms
 * @subpackage   Data
 */
class Creator
{
  /**
   * @var string
   */
  private $name;
  /**
   * @var integer
   */
  private $version;
  /**
   * @var string
   */
  private $baseDirectory;
  /**
   * @var string
   */
  private $metaSubDirectory;
  /**
   * @var string
   */
  private $websiteSubDirectory;
  /**
   * @var string
   */
  private $infoFilesSubDirectory;

  /**
   */
  public function __construct()
  {
    $this->clear();
  }

  /**
   */
  public function clear()
  {
    $this->name = null;
    $this->version = null;
    $this->baseDirectory = null;
    $this->metaSubDirectory = null;
    $this->websiteSubDirectory = null;
    $this->infoFilesSubDirectory = null;
    return $this;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param integer $version
   */
  public function setVersion($version)
  {
    $this->version = $version;
    return $this;
  }
  /**
   * @return integer
   */
  public function getVersion()
  {
    return $this->version;
  }
  /**
   * @param string $baseDirectory
   */
  public function setBaseDirectory($baseDirectory)
  {
    $this->baseDirectory = $baseDirectory;
    return $this;
  }
  /**
   * @return string
   */
  public function getBaseDirectory()
  {
    return $this->baseDirectory;
  }
  /**
   * @param string $metaSubDirectory
   */
  public function setMetaSubDirectory($metaSubDirectory)
  {
    $this->metaSubDirectory = $metaSubDirectory;
    return $this;
  }
  /**
   * @return string
   */
  public function getMetaSubDirectory()
  {
    return $this->metaSubDirectory;
  }
  /**
   * @param string $websiteSubDirectory
   */
  public function setWebsiteSubDirectory($websiteSubDirectory)
  {
    $this->websiteSubDirectory = $websiteSubDirectory;
    return $this;
  }
  /**
   * @return string
   */
  public function getWebsiteSubDirectory()
  {
    return $this->websiteSubDirectory;
  }
  /**
   * @param string $infoFilesSubDirectory
   */
  public function setInfoFilesSubDirectory($infoFilesSubDirectory)
  {
    $this->infoFilesSubDirectory = $infoFilesSubDirectory;
    return $this;
  }
  /**
   * @return string
   */
  public function getInfoFilesSubDirectory()
  {
    return $this->infoFilesSubDirectory;
  }
}
