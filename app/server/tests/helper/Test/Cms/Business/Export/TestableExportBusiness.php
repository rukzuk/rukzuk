<?php


namespace Test\Cms\Business\Export;

use Cms\Business\Export as ExportBusiness;

/**
 * This class is only for easier mocking while unit testing
 */
class TestableExportBusiness extends ExportBusiness
{
  protected $createdZipFiles = array();

  public function exportModule($websiteId, array $modulIds,
                               $zipAndRemoveDirectories = false, $complete = false)
  {
    return parent::exportModule($websiteId, $modulIds, $zipAndRemoveDirectories, $complete);
  }

  public function initExport($websiteId, $mode, $exportName = null)
  {
    return parent::initExport($websiteId, $mode, $exportName);
  }

  protected function createExportZip()
  {
    $zipFile = parent::createExportZip();
    $this->createdZipFiles[] = $zipFile;
    return $zipFile;
  }

  public function phpunitTearDown()
  {
    $this->deleteLeftoverExport();
    $this->createdZipFiles = array();
  }

  public function phpunit_getCreatedZipFiles()
  {
    return $this->createdZipFiles;
  }
}
