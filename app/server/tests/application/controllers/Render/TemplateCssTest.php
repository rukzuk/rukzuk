<?php
namespace Application\Controller\Render;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry as Registry;

/**
 * RenderController Template Css Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class TemplateCssTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  private $testDirWithResultFiles;
  private $testOutputDir;
  private $config;
  
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
  public function renderTemplateCssWithoutDataParamSuccess()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'templateId' => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('/render/templatecss/params/' . $paramsAsJson);
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
      . '/renderTemplateCssWithoutDataParamSuccess.css';
    file_put_contents($filenameActualResponse, $actualContent);

    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderTemplateCssWithoutDataParamSuccess.css';
    
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
   * Beim Render des Templates soll ein Redirect auf die Login-Maske zurueck gegeben
   * werden, wenn der User nicht angemeldet ist
   *
   * @test
   * @group integration
   */
  public function renderTemplateCssShouldRedirectToLogin()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'templateId' => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $dispatchUrl = 'render/templatecss/params/' . $paramsAsJson;
    
    $this->activateGroupCheck();
    
    $this->dispatch($dispatchUrl);

    $this->deactivateGroupCheck();

    $this->assertRedirect();

    $url = \Seitenbau\Registry::getConfig()->server->url 
      . '/login/login/?url=' . urlencode(base64_encode($dispatchUrl));
    $this->assertRedirectTo($url);
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) kein TemplateCss
   * rendern
   *
   * @test
   * @group integration
   */
  public function renderTemplateCssShouldReturnAccessDenied()
  {

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_1@sbcms.de', 'seitenbau');
    
     $params = array(
      'websiteId'   => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode'        => 'preview',
      'templateId'  => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/templatecss/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) TemplateCss rendern
   *
   * @test
   * @group integration
   */
  public function renderTemplateCssShouldReturnCss()
  {

    $this->activateGroupCheck();

    // User mit Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    
     $params = array(
      'websiteId'   => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode'        => 'preview',
      'templateId'  => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('render/templatecss/params/' . $paramsAsJson);
    
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
      . '/renderTemplateCssShouldReturnCss.css';
    file_put_contents($filenameActualResponse, $actualContent);
   
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderTemplateCssShouldReturnCss.css';
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