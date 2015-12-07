<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase;

/**
 * WebsiteController Edit Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class EditTest extends ControllerTestCase
{
  /**
   * Website editieren
   *
   * Der Test holt sich erst alle vorhanden Websites, der erste Eintrag
   * wird genutzt um die Pruefung von edit durchzufuehren
   *
   * @test
   * @group integration
   */
  public function successEditWebsite()
  {
    $website = $this->getOneWebsite();

    $params = array(
      'id' => $website->id,
      'name' => 'new name',
      'publishingenabled' => true,
      'publish' => array(
        'type' => 'internal',
        'cname' => 'my.super.domain.name.test',
      ),
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(255,255,255,1)'
        )
      ),
      'resolutions' => array(
        'enabled' => true,
        'data'    => array(
          array(
            'id' => 'res1',
            'width' => 320,
            'name'  => 'Smartphone hoch',
          ),
        ),
      ),
      'home' => 'PAGE-03565eb8-0363-47e9-afac-90ae9d96d3c2-PAGE'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // Pruefung, ob Website editiert wurde
    $this->dispatch('website/getbyid/params/{"id":"' . $website->id . '"}');
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertEquals('new name', $responseObject->data->name);
    $this->assertTrue($responseObject->data->publishingEnabled);
    $this->assertEquals($params['publish']['type'], $responseObject->data->publish->type);
    $this->assertEquals($params['publish']['cname'], $responseObject->data->publish->cname);
    $this->assertEquals(json_encode($params['resolutions']), json_encode($responseObject->data->resolutions));
    foreach ($params['colorscheme'] as $key => $expectedColorscheme)
    {
      $this->assertArrayHasKey($key, $responseObject->data->colorscheme);
      $responseColorscheme = $responseObject->data->colorscheme[$key];
      $this->assertEquals($expectedColorscheme['id'], $responseColorscheme->id);
      $this->assertEquals($expectedColorscheme['name'], $responseColorscheme->name);
      $this->assertEquals($expectedColorscheme['value'], $responseColorscheme->value);
    }
    $this->assertEquals($params['home'], $responseObject->data->home);
  }

  /**
   * @test
   * @group integration
   */
  public function noParams()
  {
    $this->dispatch('website/edit/');

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('id', $invalidKeys);
  }

  /**
   * @test
   * @group integration
   */
  public function invalidParams()
  {
    $params = array(
      'id' => 'INVALID_ID',
      'name' => 'ab',
      'colorscheme' => array(
        array(
          'invalidKey' => '1'
        )
      ),
      'home' => 'INVALID_PAGE_ID_FOR_HOME'
    );
    $paramsAsJson = json_encode($params);
    $this->dispatch('website/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    // Pflichtfelder pruefen
    $invalidKeys = array();
    foreach ($responseObject->error as $error)
    {
      $invalidKeys[$error->param->field] = $error->param->value;
    }
    $this->assertArrayHasKey('id', $invalidKeys);
    $this->assertArrayHasKey('name', $invalidKeys);
    $this->assertArrayHasKey('colorscheme', $invalidKeys);
    $this->assertArrayHasKey('home', $invalidKeys);
  }

  /**
   * @test
   * @group   integration
   * @ticket  SBCMS-572
   */
  public function editBaseDir()
  {
    $website = $this->getOneWebsite();

    $basedir = '/tmp';
    $params = array(
      'id' => $website->id,
      'publish' => array(
        'type' => 'external',
        'host' => 'sbcms-live.seitenbau.net',
        'username' => 'www-live',
        'password' => 'seitenbau',
        'basedir'  => urlencode($basedir),
        'protocol' => 'ftp'
      )
    );
    $paramsAsJson = json_encode($params);

    $this->dispatch('website/edit/params/' . $paramsAsJson);

    $response = $this->getResponseBody();

    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // Pruefung, ob Website editiert wurde
    $this->dispatch('website/getbyid/params/{"id":"' . $website->id . '"}');
    $response = $this->getResponseBody();
    $responseObject = json_decode($response);

    $this->assertSame($basedir, $responseObject->data->publish->basedir);
  }

  /**
   * Gibt eine vorhandene Website zurueck
   */
  protected function getOneWebsite()
  {
    $this->dispatch('/website/getAll');
    $response = $this->getResponseBody();

    $responseObject = json_decode($response);
    $websites = $responseObject->data->websites;

    return $websites[0];
  }

  /**
   * Benutzer ohne benoetigte Rechte duerfen die Website nicht editieren
   *
   * @test
   * @group integration
   * @dataProvider accessDeniedUser
   */
  public function editShouldReturnAccessDenied($websiteId, $username, $password, $userIswebsiteMember)
  {
    $this->activateGroupCheck();
    
    $this->assertSuccessfulLogin($username, $password);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $newName = 'name_Edit';
    
    // pruefen, ob zuordnung zur website vorhanden ist
    $request = 'website/getall';
    $this->dispatch($request);
    $responseAllWebsites = $this->getValidatedSuccessResponse();
    $hasWebsiteAccess = false;
    foreach ($responseAllWebsites->getData()->websites as $website) {
      if ($websiteId == $website->id) {
        $hasWebsiteAccess = true;
      }
    }
    if ($userIswebsiteMember && !$hasWebsiteAccess) {
      $this->fail('User isn\'t member of website '.$websiteId);
    }
    if(!$userIswebsiteMember && $hasWebsiteAccess) {
      $this->fail('User is member of website '.$websiteId);
    }
    
    $this->resetResponse();
    
    $params = array(
      'runid' => $runId,
      'id'    => $websiteId,
      'name'  => $newName,
    );
    $this->dispatch('website/edit/params/' . json_encode($params));
    
    $errorResponse = $this->getValidatedErrorResponse();
    $this->assertSame(7, $errorResponse->error[0]->code);
    
    $this->deactivateGroupCheck();
  }

  /**
   * Super-User darf alle Websites editieren
   *
   * @test
   * @group integration
   */
  public function superuserEditShouldReturnSuccess()
  {
    $params = array('id' => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE',
                    'name' => 'new name');
    $paramsAsJson = json_encode($params);
    $request = 'website/edit/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }


  /**
   * @return array
   */
  public function accessDeniedUser()
  {
    return array(
      array('SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE', 'get.all.privileges@sbcms.de', 'TEST09', false),
      array('SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5s-SITE', 'testf0@seitenbau.com', 'TEST07', true),
    );
  }  
}