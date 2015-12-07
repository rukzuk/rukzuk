<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase;

/**
 * @package      Test
 * @subpackage   Controller
 */

class EditColorschemeTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function successEditColorschemeWebsite()
  {
    $website = $this->getOneWebsite();

    $params = array(
      'id' => $website->id,
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(255,255,255,1)'
        ),
        array(
          'id' => '2',
          'name' => 'farbe2',
          'value' => 'rgba(0,0,0,0)'
        )
      ),
    );

    $this->dispatch('website/editcolorscheme/params/' . json_encode($params));
    $responseColorscheme = $this->getValidatedSuccessResponse();
    $this->assertNotNull($responseColorscheme->data);
    foreach ($params['colorscheme'] as $key => $expectedColorscheme)
    {
      $this->assertArrayHasKey($key, $responseColorscheme->data->colorscheme);
      $actualColorscheme = $responseColorscheme->data->colorscheme[$key];
      $this->assertSame($expectedColorscheme['id'], $actualColorscheme->id);
      $this->assertSame($expectedColorscheme['name'], $actualColorscheme->name);
      $this->assertSame($expectedColorscheme['value'], $actualColorscheme->value);
    }

    $this->dispatch('website/getbyid/params/{"id":"' . $website->id . '"}');
    $responseWebsite = $this->getValidatedSuccessResponse();
    $this->assertNotNull($responseWebsite->data);
    foreach ($params['colorscheme'] as $key => $expectedColorscheme)
    {
      $this->assertArrayHasKey($key, $responseWebsite->data->colorscheme);
      $actualColorscheme = $responseWebsite->data->colorscheme[$key];
      $this->assertSame($expectedColorscheme['id'], $actualColorscheme->id);
      $this->assertSame($expectedColorscheme['name'], $actualColorscheme->name);
      $this->assertSame($expectedColorscheme['value'], $actualColorscheme->value);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($invalidParams, $invalidKeys)
  {
   $this->dispatch('website/editcolorscheme/params/' . json_encode($invalidParams));
    $editResponse = $this->getValidatedErrorResponse();
    $errors = $editResponse->getError();

    $this->assertEquals(count($invalidKeys), count($errors), 'wrong error count');
    foreach ($errors as $error) {
      $this->assertContains($error->param->field, $invalidKeys);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider accessDeniedUserProvider
   */
  public function editColorschemeShouldReturnAccessDenied($websiteId, $username, $password, $userIswebsiteMember)
  {
    $this->activateGroupCheck();
    
    $this->assertSuccessfulLogin($username, $password);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $newColorscheme = array(
      array(
        'id' => '1',
        'name' => 'farbe1',
        'value' => 'rgba(0,0,0,0)'
      ),
    );
    
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
      'runid'       => $runId,
      'id'          => $websiteId,
      'colorscheme' => $newColorscheme,
    );
    $this->dispatch('website/editcolorscheme/params/' . json_encode($params));

    $response = $this->getValidatedErrorResponse();
    $this->assertSame(7, $response->error[0]->code);
    
    $this->deactivateGroupCheck();
  }

  /**
   * Super-User darf alle Websites editieren
   *
   * @test
   * @group integration
   */
  public function superuserEditColorschemeShouldReturnSuccess()
  {
    $params = array(
      'id'          => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(1,2,3,0)'
        ),
      ),
    );

    $this->activateGroupCheck();

    // Superuser ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');

    $this->dispatch('website/editcolorscheme/params/' . json_encode($params));

    $this->deactivateGroupCheck();
    
    $this->getValidatedSuccessResponse();
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
   * @return array
   */
  public function invalidParamsProvider()
  {
    return array(
      array(
        array(
          'colorscheme' => array(
            array('invalidKey' => '1'),
          ),
        ),
        array(
          'id', 'colorscheme',
        )
      ),
      array(
        array(
          'id' => array(),
          'colorscheme' => 'INVALID COLORSCHEME',
        ),
        array(
          'id', 'colorscheme',
        ),
      ),
      array(
        array(
          'colorscheme' => array(),
        ),
        array(
          'id',
        ),
      ),
      array(
        array(
          'id' => '1',
          'colorscheme' => '',
        ),
        array(
          'id', 'colorscheme',
        ),
      ),
      array(
        array(
          'id' => '2',
        ),
        array(
          'id',
        ),
      ),
    );
  }


  /**
   * @return array
   */
  public function accessDeniedUserProvider()
  {
    return array(
      array('SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE', 'get.all.privileges@sbcms.de', 'TEST09', false),
      array('SITE-eg17e89c-r2af-46cd-a6t1-fc42dc78fe5s-SITE', 'testf0@seitenbau.com', 'TEST07', true),
    );
  }  

}