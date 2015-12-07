<?php
namespace Seitenbau\Image\Type;

use Seitenbau\Image,
    Seitenbau\Registry;

/**
 * Komponententest fuer Seitenbau\Image\Adapter\PhpGd
 *
 * @package      Seitenbau
 * @subpackage   Image\Adapter\PhpGd
 */

class PhpgdTest extends \PHPUnit_Framework_TestCase
{
  protected $imageTypeHandler = null;

  protected $imageConfig = null;

  protected $imageTestFileDir = null;

  protected $imageOutputDir = null;

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
      $this->imageConfig = new \Zend_Config(array(), true);
    }
    $this->imageConfig->adapter = 'Phpgd';
    
    $this->imageOutputDir = Registry::getConfig()->test->output->imageprocessing->directory;
    if (!is_dir($this->imageOutputDir))
    {
      mkdir($this->imageOutputDir);
    }

    $this->imageTestFileDir = Registry::getConfig()->test->files->directory
      . DIRECTORY_SEPARATOR . 'imageprocessing';

    $this->imageConfig->temp_dir = $this->imageOutputDir;
    $this->imageConfig->memory_limit = 1048576;
    $this->imageTypeHandler = \Seitenbau\Image::factory($this->imageConfig->toArray());
  }

  protected function tearDown()
  {
    // Verzeichnisse wieder loeschen
    if (is_dir($this->imageOutputDir))
    {
      $this->removeDir($this->imageOutputDir);
    }
    
    parent::tearDown();
  }

  /**
   * @test
   * @group library
   * @dataProvider imageResizeProvider
   */
  public function resizeImage($memoryLimit, $sourceFileName,
    $destinationFileName, $expectedFileName, $resizeAction)
  {
    $sourceFile = $this->imageTestFileDir
        . DIRECTORY_SEPARATOR . $sourceFileName;
    $destinationFile = $this->imageOutputDir
        . DIRECTORY_SEPARATOR . $destinationFileName;
    $expectedFile = $this->imageTestFileDir
        . DIRECTORY_SEPARATOR . 'Phpgd'
        . DIRECTORY_SEPARATOR . $expectedFileName;
    
    // Speicherlimit?
    if ($memoryLimit)
    {
      // Speicherlimit auf 1KB setzen
      $this->imageTypeHandler->setMemoryLimit(1024);
    }
    else
    {
      // kein Speicherlimit verwenden
      $this->imageTypeHandler->resetMemoryLimit();
    }
    
    // Bild resizen
    $this->imageTypeHandler->setFile($sourceFile);
    $this->imageTypeHandler->loadFromGd2();
    $this->imageTypeHandler->resize($resizeAction);
    $this->imageTypeHandler->save($destinationFile, \Seitenbau\Image\Adapter\Phpgd::TYPE_GD2);

    $this->assertFileExists($destinationFile);
    $this->assertFileEquals($expectedFile, $destinationFile);
  }

  /**
   * @return  array
   */
  public function imageResizeProvider()
  {
    return array(
      // Kein Speicherlimit
      array(false, 'test_image_file.gd2', 'resized_100x100_p0_b0.gd2', 'expectedResized_100x100_p0_b0.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => false
        , 'border'      => false
      )),
      array(false, 'test_image_file.gd2', 'resized_100x100_p1_b0.gd2', 'expectedResized_100x100_p1_b0.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => false
      )),
      array(false, 'test_image_file.gd2', 'resized_100x100_p1_b1.gd2', 'expectedResized_100x100_p1_b1.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => true
      )),
      array(false, 'test_image_file.gd2', 'resized_400x100_p0_b0.gd2', 'expectedResized_400x100_p0_b0.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => false
        , 'border'      => false
      )),
      array(false, 'test_image_file.gd2', 'resized_400x100_p1_b0.gd2', 'expectedResized_400x100_p1_b0.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => false
      )),
      array(false, 'test_image_file.gd2', 'resized_400x100_p1_b1.gd2', 'expectedResized_400x100_p1_b1.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => true
      )),

      // Mit Speicherlimit
      array(true, 'test_image_file.gd2', 'resized_memlimit_100x100_p0_b0.gd2', 'expectedResized_memlimit_100x100_p0_b0.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => false
        , 'border'      => false
      )),
      array(true, 'test_image_file.gd2', 'resized_memlimit_100x100_p1_b0.gd2', 'expectedResized_memlimit_100x100_p1_b0.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => false
      )),
      array(true, 'test_image_file.gd2', 'resized_memlimit_100x100_p1_b1.gd2', 'expectedResized_memlimit_100x100_p1_b1.gd2', array(
          'width'       => 100
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => true
      )),
      array(true, 'test_image_file.gd2', 'resized_memlimit_400x100_p0_b0.gd2', 'expectedResized_memlimit_400x100_p0_b0.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => false
        , 'border'      => false
      )),
      array(true, 'test_image_file.gd2', 'resized_memlimit_400x100_p1_b0.gd2', 'expectedResized_memlimit_400x100_p1_b0.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => false
      )),
      array(true, 'test_image_file.gd2', 'resized_memlimit_400x100_p1_b1.gd2', 'expectedResized_memlimit_400x100_p1_b1.gd2', array(
          'width'       => 400
        , 'height'      => 100
        , 'proportions' => true
        , 'border'      => true
      )),
    );
  }

  /**
   * @test
   * @group library
   * @dataProvider imageCropProvider
   */
  public function cropImage($memoryLimit, $sourceFileName,
    $destinationFileName, $expectedFileName, $cropAction)
  {
    $sourceFile = $this->imageTestFileDir
        . DIRECTORY_SEPARATOR . $sourceFileName;
    $destinationFile = $this->imageOutputDir
        . DIRECTORY_SEPARATOR . $destinationFileName;
    $expectedFile = $this->imageTestFileDir
        . DIRECTORY_SEPARATOR . 'Phpgd'
        . DIRECTORY_SEPARATOR . $expectedFileName;
    
    // Speicherlimit?
    if ($memoryLimit)
    {
      // Speicherlimit auf 1MB setzen
      $this->imageTypeHandler->setMemoryLimit(1024);
    }
    else
    {
      // kein Speicherlimit verwenden
      $this->imageTypeHandler->resetMemoryLimit();
    }
    
    // Bild ausschneiden
    $this->imageTypeHandler->setFile($sourceFile);
    $this->imageTypeHandler->loadFromGd2();
    $this->imageTypeHandler->crop($cropAction);
    $this->imageTypeHandler->save($destinationFile, \Seitenbau\Image\Adapter\Phpgd::TYPE_GD2);

    $this->assertFileExists($destinationFile);
    $this->assertFileEquals($expectedFile, $destinationFile);
  }

  /**
   * @return  array
   */
  public function imageCropProvider()
  {
    return array(
      // Kein Speicherlimit
      array(false, 'test_image_file.gd2', 'croped_60_80_90x90.gd2', 'expectedCroped_60_80_90x90.gd2', array(
          'x'           => 60
        , 'y'           => 80
        , 'width'       => 90
        , 'height'      => 90
      )),
      array(false, 'test_image_file.gd2', 'croped_1_1_150x100.gd2', 'expectedCroped_1_1_150x100.gd2', array(
          'x'           => 1
        , 'y'           => 1
        , 'width'       => 150
        , 'height'      => 100
      )),

      // Mit Speicherlimit
      array(true, 'test_image_file.gd2', 'croped_memlimit_60_80_90x90.gd2', 'expectedCroped_memlimit_60_80_90x90.gd2', array(
          'x'           => 60
        , 'y'           => 80
        , 'width'       => 90
        , 'height'      => 90
      )),
      array(true, 'test_image_file.gd2', 'croped_memlimit_1_1_150x100.gd2', 'expectedCroped_memlimit_1_1_150x100.gd2', array(
          'x'           => 1
        , 'y'           => 1
        , 'width'       => 150
        , 'height'      => 100
      )),
    );
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