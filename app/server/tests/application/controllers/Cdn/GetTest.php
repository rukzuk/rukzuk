<?php
namespace Application\Controller\Cdn;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * CdnController Get Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetTest extends ControllerTestCase
{
  private $deleteFiles = array();

  protected function setUp()
  {
    parent::setUp();
  }

  public function tearDown()
  {
    if (count($this->deleteFiles) > 0)
    {
      foreach($this->deleteFiles as $file)
      {
        if (file_exists($file))
        {
          unlink ($file);
        }
      }
    }

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function paramsFailed()
  {
    $this->dispatch('/cdn/get');

    // Auf 404 Pruefen
    $this->assertSame(404, $this->getResponse()->getHttpResponseCode());
  }

  /**
   * @test
   * @group integration
   */
  public function mediaNotExists()
  {
    $params = array(
      'id'        => 'MDB-diese.id-ist.-nich-t...-vorhanden...-MDB',
      'websiteid' => 'SITE-controll-er00-cdn0-get0-test00000000-SITE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('/cdn/get/params/' . $paramsAsJson);

    $this->assertSame(404, $this->getResponse()->getHttpResponseCode());
  }

  /**
   * @test
   * @group integration
   */
  public function mediaFileNotExists()
  {
    $params = array(
      'id'        => 'MDB-00000000-0000-0000-0000-000000000001-MDB',
      'websiteid' => 'SITE-controll-er00-cdn0-get0-test00000000-SITE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('/cdn/get/params/' . $paramsAsJson);

    $this->assertSame(404, $this->getResponse()->getHttpResponseCode());
  }

  /**
   * @test
   * @group integration
   */
  public function success()
  {
    $params = array(
      'id'        => 'MDB-00000000-0000-0000-0000-000000000002-MDB',
      'websiteid' => 'SITE-controll-er00-cdn0-get0-test00000000-SITE',
      'type'      => 'image'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/get/params/' . $paramsAsJson);

    $this->assertSame(200, $this->getResponse()->getHttpResponseCode());

    // Erwarteter Content ermitteln
    $filenameExpectedResponse = Registry::getConfig()->media->files->directory
      .DIRECTORY_SEPARATOR.$params['websiteid']
      .DIRECTORY_SEPARATOR.'test02.jpg';
    if (!file_exists($filenameExpectedResponse))
    {
      throw new \Exception('Testfile "' . $filenameExpectedResponse . '" zur Pruefung des Ergebnis nicht vorhanden');
    }
    $expectedContent = file_get_contents($filenameExpectedResponse);

    // same content
    $callbackOutput = $this->getResponse()->getTestCallbackOutput();
    $this->assertSame($expectedContent, $callbackOutput[0]);
  }
}