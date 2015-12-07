<?php
namespace Test\Seitenbau;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Directory\Helper as DirectoryHelper,
    Seitenbau\Ftp as FtpClient,
    Test\Seitenbau\System\Helper as SystemHelper;

/**
 * BuilderControllerTestCase
 *
 * @package      Test
 * @subpackage   Seitenbau
 */
class BuilderControllerTestCase extends ControllerTestCase
{
  protected function removeWebsiteBuilds()
  {
    $config = Registry::getConfig();
    $buildsDirectory = $config->builds->directory;
    
    DirectoryHelper::removeRecursiv($buildsDirectory);
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
  protected function createWebsiteBuildsDirectory($websiteId)
  {
    $config = Registry::getConfig();
    $buildsDirectory = $config->builds->directory;
    
    if (!is_dir($buildsDirectory)) {
      mkdir($buildsDirectory);
    }
    
    $websiteBuildsDirectory = $buildsDirectory 
      . DIRECTORY_SEPARATOR . $websiteId; 
    
    if (!is_dir($websiteBuildsDirectory)) {
      mkdir($websiteBuildsDirectory);
    }
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
   * @param string $directory 
   */
  protected function removeTestFtpDirectory($directory)
  { 
    $config = Registry::getConfig();
    $publishConfig = $config->publish;
    
    $ftpClient = new FtpClient($publishConfig, Registry::getLogger());
    $assertionMessageClient = sprintf(
      "FTP connection to '%s' failed", $publishConfig->get('host')
    );
    $this->assertTrue( ($ftpClient->connect() !== null), $assertionMessageClient);

    $assertionMessageRemoveDir = sprintf(
      "FTP remove directory '%s' failed", $directory
    );
    $this->assertTrue( $ftpClient->removeDirectory($directory), $assertionMessageRemoveDir );
    $ftpClient->close();
    
    return true;
  }
  
  /**
   * @return string The created FTP test directory
   */
  protected function createTestFtpDirectory()
  {
    $config = Registry::getConfig();
    $publishConfig = $config->publish;
    
    $testFtpDirectory = $config->publish->get('basedir')
      . FtpClient::FTP_DIRECTORY_SEPARATOR . 'test_dir_' . time();
    
    $ftpClient = new FtpClient($publishConfig, Registry::getLogger());
    $assertionMessageClient = sprintf(
      "FTP connection to '%s' failed", $publishConfig->get('host')
    );
    $this->assertTrue( ($ftpClient->connect() !== null), $assertionMessageClient);


    $assertionMessageCreateDir = sprintf(
      "FTP create directory '%s' failed", $testFtpDirectory
    );
    $this->assertTrue( $ftpClient->createDirectory($testFtpDirectory), $assertionMessageCreateDir );
    $ftpClient->close();

    return $testFtpDirectory;
  }
}