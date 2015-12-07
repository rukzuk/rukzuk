<?php
namespace Test\Seitenbau;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\System\Helper as SystemHelper;

/**
 * PublisherServiceTestCase
 *
 * @package      
 * @subpackage   
 */
class PublisherServiceTestCase extends ServiceTestCase
{
  /**
   * @var string
   */
  protected $websiteCreatorDirectory = null;
  /**
   * @var string
   */
  protected $websiteBuildsDirectory = null;
  
  /**
   * @param  string $websiteId
   * @return string 
   */
  protected function getWebsiteCreatorDirectory($websiteId)
  {
    if ($this->websiteCreatorDirectory === null) {
    
      $config = Registry::getConfig();
      $creatorDirectory = $config->creator->directory;
      $this->websiteCreatorDirectory = $creatorDirectory 
        . DIRECTORY_SEPARATOR . $websiteId;
    }
    
    return $this->websiteCreatorDirectory;
  }
  /**
   * @param  string $websiteId
   * @return string 
   */
  protected function getWebsiteBuildsDirectory($websiteId)
  {
    if ($this->websiteBuildsDirectory === null) {
    
      $config = Registry::getConfig();
      $buildsDirectory = $config->builds->directory;
      $this->websiteBuildsDirectory = $buildsDirectory 
        . DIRECTORY_SEPARATOR . $websiteId;
    }
    return $this->websiteBuildsDirectory;
  }
  
  protected function removeWebsiteBuilds()
  {
    $config = Registry::getConfig();
    $buildsDirectory = $config->builds->directory;
    
    DirectoryHelper::removeRecursiv($buildsDirectory);
  }
  
  /**
   * @param string $websiteId 
   */
  protected function copyWebsiteBuildsFromStorageToBuildsDirectory($websiteId)
  {
    $config = Registry::getConfig();
    $buildsDirectory = $config->builds->directory;
    
    $websiteBuildsDirectory = $buildsDirectory 
      . DIRECTORY_SEPARATOR . $websiteId; 
    $websiteBuildsStorageDirectory = $config->test->builds->storage->directory 
      . DIRECTORY_SEPARATOR . $websiteId;
    
    if (is_dir($websiteBuildsStorageDirectory)) {
      if (!is_dir($buildsDirectory)) {
        mkdir($buildsDirectory);
      }
      if (!is_dir($websiteBuildsDirectory)) {
        mkdir($websiteBuildsDirectory);
      }
      $copyCommand = sprintf('cp -r %s %s', 
        $websiteBuildsStorageDirectory, 
        $buildsDirectory
      );
      SystemHelper::user_proc_exec($copyCommand);
    }
  }
}