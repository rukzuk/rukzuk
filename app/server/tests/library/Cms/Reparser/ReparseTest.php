<?php
namespace Cms\Reparser;

use Cms\Reparser\UnitExtractor as CmsUnitExtractor,
    Cms\Service\Page as PageService,
    Cms\Service\Template as TemplateService,
    Cms\Reparser as CmsReparser;
use \Test\Rukzuk\AbstractTestCase;

/**
 * Komponententest für Cms\Reparser -> Reparse Funktion
 *
 * @package      Cms
 * @subpackage   Reparser
 */

class ReparseTest extends AbstractTestCase
{
  protected $page;

  protected $template;
  
  protected $websiteId = 'SITE-a344abb2-2a96-4836-b847-1ab0571b1e6d-SITE';

  public function setUp()
  {
    parent::setUp();

    $pageService = new PageService('Page');
    $this->page = $pageService->getById(
      'PAGE-033d84e8-cc3e-4a1f-a408-b8fa374af75f-PAGE',
      $this->websiteId
    );

    $templateService = new TemplateService('Template');
    $this->template = $templateService->getById(
      'TPL-0db7eaa7-7fc5-464a-bd47-16b3b8af67eb-TPL',
      $this->websiteId
    );
  }

  protected function tearDown()
  {
    if (\Seitenbau\Registry::getConfig()->screens->activ != false
        && \Seitenbau\Registry::getConfig()->screens->activ != "no"
    ){
      $screenshotWebsiteDir = \Seitenbau\Registry::getConfig()->screens->directory .
                    DIRECTORY_SEPARATOR . $this->websiteId . DIRECTORY_SEPARATOR;
      $this->removeDir($screenshotWebsiteDir);
    }
    
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   */
  public function getUnitsFromPageContentSuccess()
  {
    $reparser = new CmsReparser();
    $result = $reparser->reparseAndUpdatePage($this->page, $this->template);
    $this->assertTrue($result);
  }

  /**
   * Loescht ein Verzeichnis samt Inhalt (Dateien und Unterordner)
   *
   * @param string $websiteDir
   */
  private function removeDir($dir)
  {
    if (\is_dir($dir))
    {
      $dirHandle = opendir($dir);
      while(($file = \readdir($dirHandle)) !== false)
      {
        if ($file == '.' || $file == '..') continue;
        $handle = $dir . DIRECTORY_SEPARATOR . $file;

        $filetype = filetype($handle);

        if ($filetype == 'dir')
        {
          $this->removeDir($handle);
        }
        else
        {
          unlink($handle);
        }
      }
      closedir($dirHandle);
      rmdir($dir);
    }
  }
}