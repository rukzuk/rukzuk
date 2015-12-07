<?php
namespace Application\Controller\Render;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry as Registry;

/**
 * RenderController Page Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class PageTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  private $testDirWithResultFiles;
  private $testOutputDir;

  protected function setUp()
  {
    parent::setUp();
    
    $this->config = Registry::getConfig();
    $renderTestDir = $this->config->test->response->render->directory;
    $renderTestOutputDir = $this->config->test->output->response->render->directory;

    $this->testDirWithResultFiles = $renderTestDir;
    $this->testOutputDir = $renderTestOutputDir;
  }

  /**
   * @test
   * @group integration
   */
  public function renderPageWithoutDataParamSuccess()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'pageId' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/page/params/' . $paramsAsJson);
    $actualContent = $this->getResponseBody();

    $filenameActualResponse = $this->testOutputDir 
      . '/renderPageWithoutDataParamSuccess.html';
    file_put_contents($filenameActualResponse, $actualContent);
    
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderPageWithoutDataParamSuccess.html';
    
    if (!file_exists($filenameExpectedResponse))
    {
      throw new \Exception('Testfile "' . $filenameExpectedResponse . '" zur Pruefung des Ergebnis nicht vorhanden');
    }
    
    $expectedContent = file_get_contents($filenameExpectedResponse);
    
    $expectedContent = str_replace(
      '@@CMS_WEBPATH@@', 
      $this->config->test->cms_webpath,
      $expectedContent
    );
    
    $this->assertSame($expectedContent, $actualContent);
  }

  /**
   * @test
   * @group integration
   */
  public function renderPageWithDataParamSuccess()
  {
    $this->dispatch('render/page/params/{"websiteId":"SITE-renderer-site-test-1234-mjsncgmjszt1-SITE","pageId":"PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE","mode":"edit","data":"[{\"id\":\"MUNIT-0d15e1d0-301e-450e-b9b9-3832cb570e70-MUNIT\",\"moduleId\":\"MODUL-065b60a4-887e-4183-ae21-7ecfd6bfdfb9-MODUL\",\"name\":\"Text\",\"description\":\"\",\"icon\":\"page_white_text.png\",\"formValues\":{\"text\":\"\",\"margintop\":5,\"marginbottom\":5},\"expanded\":true,\"deletable\":false,\"ghostContainer\":false,\"visibleFormGroups\":[]}]"}');
    $content = $this->getResponseBody();

    $filenamerResponse = $this->testOutputDir . '/renderPageWithDataParamSuccess.html';
    $handle = fopen($filenamerResponse, 'w');
    fwrite($handle, $content);
    fclose($handle);

    $filename = $this->testDirWithResultFiles . '/renderPageWithoutDataParamSuccess.html';
    if (!file_exists($filename))
    {
      throw new \Exception('Testfile "' . $filename . '" zur Pruefung des Ergebnis nicht vorhanden');
    }
    
    $handle = fopen($filename, 'r');
    $notExpectedContent = fread($handle, filesize($filename));
    fclose($handle);
    
    $this->assertNotSame($notExpectedContent, $content, 'Content der Page wurde gerendert');

    $filename = $this->testDirWithResultFiles . '/renderPageWithDataParamSuccess.html';
    $handle = fopen($filename, 'r');
    $expectedContent = fread($handle, filesize($filename));
    fclose($handle);
    $this->assertSame($expectedContent, $content);
  }

  /**
   * Beim Render der Page soll ein Redirect auf die Login-Maske zurueck gegeben
   * werden, wenn der User nicht angemeldet ist
   *
   * @test
   * @group integration
   */
  public function renderShouldRedirectToLogin()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'pageId' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $dispatchUrl = 'render/page/params/' . $paramsAsJson;
    
    $this->activateGroupCheck();

    $this->dispatch($dispatchUrl);

    $this->deactivateGroupCheck();
    
    $this->assertRedirect();

    $url = \Seitenbau\Registry::getConfig()->server->url 
      . '/login/login/?url=' . urlencode(base64_encode($dispatchUrl));
    $this->assertRedirectTo($url);
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) keine Page
   * rendern
   *
   * @test
   * @group integration
   */
  public function renderPageShouldReturnAccessDenied()
  {

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_1@sbcms.de', 'seitenbau');
    
     $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode'      => 'preview',
      'pageId'    => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/page/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) Page rendern
   *
   * @test
   * @group integration
   */
  public function renderPageShouldReturnHtml()
  {

    $this->activateGroupCheck();

    // User mit Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    
     $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode'      => 'preview',
      'pageId'    => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/page/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();

    $actualContent = $this->getResponseBody();
    $actualHeader = $this->getResponse()->getHeaders();

    // Content-Typ "text/css" vorhanden
    $actualContentTyp = '';
    if (is_array($actualHeader))
    {
      foreach ($actualHeader as $nextHeader)
      {
        if (strtolower($nextHeader['name']) == 'content-type')
        {
          $actualContentTyp = $nextHeader['value'];
        }
      }
    }

    // Content pruefen
    $filenameActualResponse = $this->testOutputDir 
      . '/renderPageShouldReturnHtml.html';
    file_put_contents($filenameActualResponse, $actualContent);
   
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderPageShouldReturnHtml.html';
    if (!file_exists($filenameExpectedResponse))
    {
      throw new \Exception('Testfile "' . $filenameExpectedResponse . '" zur Pruefung des Ergebnis nicht vorhanden');
    }
    $expectedContent = file_get_contents($filenameExpectedResponse);
    
    $expectedContent = str_replace(
      '@@CMS_WEBPATH@@', 
      $this->config->test->cms_webpath,
      $expectedContent
    );
    
    $this->assertSame($expectedContent, $actualContent);
  }  
}