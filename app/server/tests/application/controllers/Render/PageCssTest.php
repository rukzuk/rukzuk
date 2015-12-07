<?php
namespace Application\Controller\Render;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry as Registry;

/**
 * RenderController PageCss Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class PageCssTest extends ControllerTestCase
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
  public function renderPageCssWithoutDataParamSuccess()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'pageId' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/pagecss/params/' . $paramsAsJson);
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
    $this->assertSame($actualContentTyp, 'text/css', 'Contetn-Type muss "text/css" sein');

    // Content pruefen
    $filenameActualResponse = $this->testOutputDir 
      . '/renderPageCssWithoutDataParamSuccess.css';
    file_put_contents($filenameActualResponse, $actualContent);
    
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderPageCssWithoutDataParamSuccess.css';
    
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
   * Beim Render des Page CSS soll ein Redirect auf die Login-Maske zurueck gegeben
   * werden, wenn der User nicht angemeldet ist
   *
   * @test
   * @group integration
   */
  public function renderPageCssShouldRedirectToLogin()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'pageId' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $dispatchUrl = 'render/pagecss/params/' . $paramsAsJson;
    
    $this->activateGroupCheck();

    $this->dispatch($dispatchUrl);

    $this->deactivateGroupCheck();
    
    $this->assertRedirect();

    $url = \Seitenbau\Registry::getConfig()->server->url 
      . '/login/login/?url=' . urlencode(base64_encode($dispatchUrl));
    $this->assertRedirectTo($url);
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) kein PageCss
   * rendern
   *
   * @test
   * @group integration
   */
  public function renderPageCssShouldReturnAccessDenied()
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
    $this->dispatch('render/pagecss/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) PageCss rendern
   *
   * @test
   * @group integration
   */
  public function renderPageCssShouldReturnCss()
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
    $this->dispatch('render/pagecss/params/' . $paramsAsJson);
    
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
      . '/renderPageCssShouldReturnCss.css';
    file_put_contents($filenameActualResponse, $actualContent);
   
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderPageCssShouldReturnCss.css';
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