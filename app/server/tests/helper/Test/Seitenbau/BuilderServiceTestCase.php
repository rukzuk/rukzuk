<?php
namespace Test\Seitenbau;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Test\Seitenbau\System\Helper as SystemHelper;

/**
 * BuilderServiceTestCase
 *
 * @package      
 * @subpackage   
 */
class BuilderServiceTestCase extends ServiceTestCase
{
  protected $creatorName = 'PhpCreator';

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
        . DIRECTORY_SEPARATOR . $websiteId . '-' . $this->creatorName;
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
  
  protected function removeWebsiteIndexes()
  {
    $config = Registry::getConfig();
    $indexDirectory = $config->indexing->basedir;
    
    DirectoryHelper::removeRecursiv($indexDirectory);
  }
  
  protected function removeCreatedWebsite($websiteId, $creatorType=null)
  {
    $config = Registry::getConfig();
    
    if (empty($creatorType)) {
      $creatorType = $config->creator->defaultCreator;
    }
    
    $creatorDirectory = $config->creator->directory;
    $websiteCreatorDir = $creatorDirectory
      . DIRECTORY_SEPARATOR . $websiteId . '-' . $creatorType;
    
    DirectoryHelper::removeRecursiv($websiteCreatorDir, $creatorDirectory);
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
  /**
   * @param  mixed $value
   * @return mixed
   */
  protected function changeConfiguredIndexingEnabled($value)
  {
    $formerIndexingEnabled = Registry::getConfig()->indexing->enabled;
    $this->mergeIntoConfig(array('indexing' => array('enabled' => $value)));
    $this->assertEquals($value, Registry::getConfig()->indexing->enabled);
    return $formerIndexingEnabled;
  }
  /**
   * @param  mixed $indexer
   * @return mixed
   */
  protected function changeConfiguredIndexer($indexer)
  {
    $formerIndexer = Registry::getConfig()->indexing->indexer;
    $this->mergeIntoConfig(array('indexing' => array('indexer' => $indexer)));
    $this->assertEquals($indexer, Registry::getConfig()->indexing->indexer);
    return $formerIndexer;
  }
}