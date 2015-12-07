<?php
namespace Seitenbau\Image;

use Seitenbau\Registry;

/**
 * Testen der Standard-Funktionen der Base-Klasse von Image
 *
 * @package      Seitenbau
 * @subpackage   Image\Base
 */

class BaseTest extends \PHPUnit_Framework_TestCase
{
  private $imageConfig = null;

  protected function setUp()
  {
    // Parent aufrufen
    parent::setUp();

    // Konfiguration ermitteln
    $orgConfig = Registry::getConfig()->imageprocessing;
    if ($orgConfig instanceof \Zend_Config)
    {
      $this->imageConfig = clone($orgConfig);
    }
    else
    {
      $this->imageConfig = new \Zend_Config(array());
    }

    // positiv Test mit gueltiger Konfiguration
    $validBase = \Seitenbau\Image::factory($this->imageConfig->toArray());
    $this->assertInstanceOf('Seitenbau\Image\Base', $validBase);
  }
  
  /**
   * @test
   * @group library
   */
  public function setImageFile()
  {

    $testFilesDirectory = Registry::getConfig()->test->files->directory;
    $imageFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'imageprocessing'
      . DIRECTORY_SEPARATOR . 'test_image_file.jpg';
    
    $imageTool = \Seitenbau\Image::factory($this->imageConfig->toArray());
    $imageTool->setFile($imageFile);
    $this->assertInstanceOf('Seitenbau\Image\Base', $imageTool);
  }


  /**
   * @test
   * @group library
   */
  public function setImageFileShouldThrowExceptionOnNonExistingImagefile()
  {
    $testFilesDirectory = Registry::getConfig()->test->files->directory;
    $imageFile = $testFilesDirectory
      . DIRECTORY_SEPARATOR . 'imageprocessing'
      . DIRECTORY_SEPARATOR . 'not_existing_file.jpg';

    $imageTool = \Seitenbau\Image::factory($this->imageConfig->toArray());

    $this->setExpectedException('\Seitenbau\Image\ImageException');
    $imageTool->setFile($imageFile);
  }

}