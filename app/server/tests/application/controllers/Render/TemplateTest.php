<?php
namespace Application\Controller\Render;

use Test\Seitenbau\ControllerTestCase,
    Seitenbau\Registry as Registry;

/**
 * RenderController Template Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class TemplateTest extends ControllerTestCase
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
  public function renderTemplateWithoutDataParamSuccess()
  {
    $params = array(
      'websiteId' => 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE',
      'mode' => 'edit',
      'templateId' => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('/render/template/params/' . $paramsAsJson);
    $actualContent = $this->getResponseBody();
    
    $filenameActualResponse = $this->testOutputDir 
      . '/renderTemplateWithoutDataParamSuccess.html';
    file_put_contents($filenameActualResponse, $actualContent);

    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderTemplateWithoutDataParamSuccess.html';
    
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
  public function renderTemplateWithDataParamSuccess()
  {
    // ARRANGE
    $websiteId = 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE';
    $templateId = 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL';
    $templateContent = '[{"id":"MUNIT-0d15e1d0-301e-450e-b9b9-3832cb570e70-MUNIT","moduleId":"MODUL-065b60a4-887e-4183-ae21-7ecfd6bfdfb9-MODUL","name":"Text","description":"","icon":"page_white_text.png","formValues":{"text":"","margintop":5,"marginbottom":5},"expanded":true,"deletable":false,"ghostContainer":false,"visibleFormGroups":[]}]';

    // ACT
    $this->dispatchWithParams('/render/template', array(
      'websiteId' => $websiteId,
      'templateId' => $templateId,
      'mode' => 'edit',
      'data' => $templateContent,
    ));
    $content = $this->getResponseBody();

    // ASSERT
    $filenameResponse = $this->testOutputDir . '/renderTemplateWithDataParamSuccess.html';
    $handle = fopen($filenameResponse, 'w');
    fwrite($handle, $content);
    fclose($handle);

    $filename = $this->testDirWithResultFiles . '/renderTemplateWithoutDataParamSuccess.html';
    if (!file_exists($filename))
    {
      throw new \Exception('Testfile "' . $filename . '" zur Pruefung des Ergebnis nicht vorhanden');
    }
    $handle = fopen($filename, 'r');
    $notExpectedContent = fread($handle, filesize($filename));
    fclose($handle);
    $this->assertNotSame($notExpectedContent, $content, 'Content des Templates wurde gerendert');

    $filename = $this->testDirWithResultFiles . '/renderTemplateWithDataParamSuccess.html';
    $handle = fopen($filename, 'r');
    $expectedContent = fread($handle, filesize($filename));
    fclose($handle);
    $this->assertSame($expectedContent, $content);
  }

  /**
   * Beim Render des Templates soll ein Redirect auf die Login-Maske zurueck gegeben
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
      'templateId' => 'TPL-renderer-temp-late-test-nhs61g7g54sm-TPL'
    );
    $paramsAsJson = json_encode($params);
    $dispatchUrl = 'render/template/params/' . $paramsAsJson;
    
    $this->activateGroupCheck();
    
    $this->dispatch($dispatchUrl);

    $this->deactivateGroupCheck();

    $this->assertRedirect();

    $url = \Seitenbau\Registry::getConfig()->server->url 
      . '/login/login/?url=' . urlencode(base64_encode($dispatchUrl));
    $this->assertRedirectTo($url);
  }

  /**
   * User darf ohne Website-Zugehoerigkeit (ueber eine Gruppe) kein Template
   * rendern
   *
   * @test
   * @group integration
   */
  public function renderTemplateShouldReturnAccessDenied()
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
    $this->dispatch('render/template/params/' . $paramsAsJson);
    
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * User darf mit Website-Zugehoerigkeit (ueber eine Gruppe) Template rendern
   *
   * @test
   * @group integration
   */
  public function renderTemplateShouldReturnHtml()
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
    $this->dispatch('render/template/params/' . $paramsAsJson);
    
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
      . '/renderTemplateShouldReturnHtml.html';
    file_put_contents($filenameActualResponse, $actualContent);
   
    $filenameExpectedResponse = $this->testDirWithResultFiles 
      . '/renderTemplateShouldReturnHtml.html';
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