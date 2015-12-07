<?php
namespace Application\Controller\Cdn;

use Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;

/**
 * CdnController Export Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetScreenTest extends ControllerTestCase
{
  private $deleteFiles = array();

  /**
   * @var \Cms\Service\Screenshot
   */
  private $screenshotService;

  protected function setUp()
  {
    parent::setUp();

    $this->markTestSkipped('Disabled because of memory problems');

    // Screenshots in Registry aktivieren
    Registry::getConfig()->screens->activ = 1;

    $this->screenshotService = new \Cms\Service\Screenshot;
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
    $this->dispatch('/cdn/getscreen');

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);

    $this->assertSame(400, $this->getResponse()->getHttpResponseCode());
  }

  /**
   * @test
   * @group integration
   */
  public function success()
  {
    $params = array(
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'id' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE',
      'type' => 'page'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $filename = $this->getStandardCacheFileName($params['websiteid'], $params['id']);
    $this->deleteFiles[] = $filename;

    // warten bis zur Screenshot erstellung
    $this->assertFileExistsWithDelay($filename);
  }

  /**
   * Prueft, ob die angegebene Datei existiert, die Pruefung wird mehrmals
   * durchgefuehrt, bis die max Wartezeit verstrichen ist
   *
   * @param type $filename
   * @param int $delayInSec
   * @param int $waitIsRequired Gibt an, ob die Wartezeit bis zur Pruefung Pflicht ist
   * @return type
   */
  private function assertFileExistsWithDelay($filename, $delayInSec = 30,
    $waitIsRequired = false
  ){
    if ($waitIsRequired == true)
    {
      sleep($waitIsRequired);
      $delayInSec = 0;
    }
    while (!file_exists($filename) && $delayInSec > 0)
    {
      sleep(1);
      $delayInSec--;
    }

    $this->assertFileExists($filename, 'Screen wurde nicht gefunden: ' . $filename);
  }


  /**
   * @test
   * @group integration
   */
  public function resourceFileNotExists()
  {
    Registry::getConfig()->screens->systemcallwkhtmltoimage->wait->response = 0;

    $params = array(
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'id' => 'PAGE-notexist-page-1472-47du-m7j9klswmc71-PAGE',
      'type' => 'page'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $this->assertSame(404, $this->getResponse()->getHttpResponseCode());

    $filename = $this->getStandardCacheFileName($params['websiteid'], $params['id']);

    $this->assertFileNotExists($filename);
    $this->deleteFiles[] = $filename;
  }

  /**
   * @test
   * @group integration
   */
  public function getWebsiteScreen()
  {
    $firstPageInWebsite = 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE';

    $params = array(
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'type' => 'website'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $filename = $this->getStandardCacheFileName($params['websiteid'],
      $firstPageInWebsite);

    $this->assertFileExistsWithDelay($filename);
    $this->deleteFiles[] = $filename;
  }

  /**
   * @test
   * @group integration
   */
  public function getWebsiteScreenInDeclaredSize()
  {
    $firstPageInWebsite = 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE';

    $params = array(
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'type' => 'website',
      'width' => 40,
      'height' => 50
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    $filename = $this->getStandardCacheFileName($params['websiteid'],
      $firstPageInWebsite, $params['width'], $params['height']);

    $this->assertFileExistsWithDelay($filename);
    $this->deleteFiles[] = $filename;

    $imageSize = getimagesize($filename);
    $this->assertSame($params['width'], $imageSize[0],
      'Bildabmessungen wurden nicht berücksichtigt');
    $this->assertSame($params['height'], $imageSize[1],
      'Bildabmessungen wurden nicht berücksichtigt');
  }

  /**
   * @test
   * @group integration
   */
  public function pageAndWebsiteNotExists()
  {
    $firstPageInWebsite = 'PAGE-renderer-page-5843-4m8h-2l0a69cd232a-PAGE';

    $params = array(
      'websiteid' => 'SITE-renderer-site-test-582s-se2vv29ki9a-SITE',
      'type' => 'website'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $filename = $this->getStandardCacheFileName($params['websiteid'],
      $firstPageInWebsite);

    $this->assertFileNotExists($filename, 'Datei darf nicht existieren, da Website und Page nicht existieren');

    $verzInfos = explode(DIRECTORY_SEPARATOR, $filename);
    array_pop($verzInfos);
    $verzeichnis = implode(DIRECTORY_SEPARATOR, $verzInfos);
    $this->assertFileNotExists($verzeichnis);
    $verzInfos = explode(DIRECTORY_SEPARATOR, $verzeichnis);
    array_pop($verzInfos);
    $verzeichnis = implode(DIRECTORY_SEPARATOR, $verzInfos);
    $this->assertFileNotExists($verzeichnis);
    array_pop($verzInfos);
    $verzeichnis = implode(DIRECTORY_SEPARATOR, $verzInfos);
    $this->assertFileNotExists($verzeichnis);
  }

  /**
   * @test
   * @group integration
   */
  public function pageNotExistsAndWebsiteExists()
  {
    $params = array(
      'websiteid' => 'SITE-renderer-site-test-with-no2pagejszt1-SITE',
      'type' => 'page',
      'id' => 'PAGE-renderer-page-not1-exis-tl0a69cd232a-PAGE'
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('/cdn/getscreen/params/' . $paramsAsJson);

    $filename = $this->getStandardCacheFileName($params['websiteid'], $params['id']);

    $this->assertFileNotExists($filename, 'Datei darf nicht existieren, da Website und Page nicht existieren');

    $verzInfos = explode(DIRECTORY_SEPARATOR, $filename);
    array_pop($verzInfos);
    $verzeichnis = implode(DIRECTORY_SEPARATOR, $verzInfos);
    $this->assertFileNotExists($verzeichnis, 'Verzeichnis zur Page, die nicht existiert, darf nicht angelegt werden');
  }

  /**
   * Gibt den Dateinamen samt Pfad der Cache-Datei zurueck
   *
   * @param string $websiteId
   * @param string $pageId
   * @param int $width
   * @param int $height
   *
   * @return string
   */
  private function getStandardCacheFileName($websiteId, $pageId, $width = '',
    $height = ''
  ){
    $width = ($width == '')
           ? Registry::getConfig()->screens->thumbnail->width
           : (int) $width;

    $height = ($height == '')
           ? Registry::getConfig()->screens->thumbnail->height
           : (int) $height;

    $filename = Registry::getConfig()->screens->cache->directory . DIRECTORY_SEPARATOR .
      $websiteId . DIRECTORY_SEPARATOR .
      'pages' . DIRECTORY_SEPARATOR .
      $pageId . DIRECTORY_SEPARATOR .
      $width . 'x' . $height . '.' . Registry::getConfig()->screens->filetype;

    return $filename;
  }

  /**
   * Gibt den Pfad zur Original Datei zurueck
   *
   * @param string $websiteId
   * @param string $pageId
   * @return string
   */
  private function getStandardOriginFileName($websiteId, $pageId)
  {
    $filename = Registry::getConfig()->screens->directory . DIRECTORY_SEPARATOR .
      $websiteId . DIRECTORY_SEPARATOR .
      'pages' . DIRECTORY_SEPARATOR .
      $pageId . '.' . Registry::getConfig()->screens->filetype;

    return $filename;
  }

  /**
   * @test
   * @group integration
   */
  public function updateAnExistScreenshot()
  {
    $firstPageInWebsite = 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE';
    $websiteId = 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE';

    $screenFile = $this->getStandardCacheFileName($websiteId,
      $firstPageInWebsite);

    $params = array(
      'websiteid' => $websiteId,
      'type' => 'website'
    );
    $dispatchGetScreenUrl = '/cdn/getscreen/params/' . json_encode($params);
    $this->dispatch($dispatchGetScreenUrl);

    $this->assertFileExistsWithDelay($screenFile);

    $this->deleteFiles[] = $screenFile;

    $timestampBeforeUpdatePage = filemtime($screenFile);

    // kurz warten, damit ein anderer Timestamp beim Update gefuehrt wird
    sleep(1);

    // Page editieren
    $paramsEditPage = array(
      'runid' => 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID',
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'id' => $firstPageInWebsite,
      'name' => 'new page name'
    );

    $this->activateGroupCheck();
    $this->assertSuccessfulLogin('cdn.getscreen@sbcms.de', 'TEST01');
    $this->assertSuccessfulLock($paramsEditPage['runid'], $paramsEditPage['id'],
                                $paramsEditPage['websiteid'], 'page');
    $this->dispatch('/page/edit/params/' . json_encode($paramsEditPage));
    $this->deactivateGroupCheck();
    $this->assertSuccessfulUnlock($paramsEditPage['runid'], $paramsEditPage['id'],
                                  $paramsEditPage['websiteid'], 'page');

    // Screen abrufen
    $this->dispatch($dispatchGetScreenUrl);

    // Screen muss sich durch Edit aktualisert haben
    $this->assertGreaterThan($timestampBeforeUpdatePage, filemtime($screenFile),
      'Screenshot wurde nicht aktualisiert');
  }

  /**
   * @test
   * @group integration
   */
  public function notUpdateAnExistScreenshotWhenScreenNotActiv()
  {
    $firstPageInWebsite = 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE';
    $websiteId = 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE';

    $screenFile = $this->getStandardCacheFileName($websiteId,
      $firstPageInWebsite);

    $params = array(
      'websiteid' => $websiteId,
      'type' => 'website'
    );
    $dispatchGetScreenUrl = '/cdn/getscreen/params/' . json_encode($params);
    $this->dispatch($dispatchGetScreenUrl);

    
    $response = $this->getResponse();
    $this->assertSame(200, $response->getHttpResponseCode());
    
    $hasContentTypeJpeg = false;
    $headers = $response->getHeaders();
    foreach ($headers as $header)
    {
      if ($header['name'] == 'Content-Type')
      {
        if ($header['value'] != 'image/jpeg')
        {
          var_dump($response->getBody());
        }
        $this->assertSame('image/jpeg', $header['value']);
        $hasContentTypeJpeg = true;
        break;
      }
    }
    $this->assertSame(true, $hasContentTypeJpeg);
    
    $this->assertFileExistsWithDelay($screenFile);

    $this->deleteFiles[] = $screenFile;

    $timestampBeforeUpdatePage = filemtime($screenFile);

    // kurz warten, damit ein anderer Timestamp beim Update gefuehrt wird
    sleep(1);

    // Screenshots deaktivieren -> kein neuer Screenshot wird erstellt
    Registry::getConfig()->screens->activ = 0;

    // Page editieren
    $paramsEditPage = array(
      'websiteid' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'id' => $firstPageInWebsite,
      'name' => 'new page name'
    );
    $this->dispatch('/page/edit/params/' . json_encode($paramsEditPage));

    // Screen abrufen
    $this->dispatch($dispatchGetScreenUrl);

    // Screen muss sich durch Edit aktualisert haben
    $this->assertSame($timestampBeforeUpdatePage, filemtime($screenFile),
      'Screenshot wurde nicht aktualisiert');
  }
}