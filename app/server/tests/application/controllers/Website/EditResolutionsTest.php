<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase;

/**
 * @package      Test
 * @subpackage   Controller
 */

class EditResolutionsTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function successEditResolutions()
  {
    $website = $this->getOneWebsite();
    
    $params = (object) array(
      'id' => $website->id,
      'resolutions' => (object) array(
        'enabled' => true,
        'data'    => array(
          (object) array(
            'id'    => 'res1',
            'width' => 480,
            'name'  => 'Smartphone quer',
          ),
          (object) array(
            'id'    => 'res2',
            'width' => 320,
            'name'  => 'Smartphone hoch',
          ),
        ),
      ),
    );

    $this->dispatch('website/editresolutions/params/' . json_encode($params));
    $responseResolutions = $this->getValidatedSuccessResponse();
    $responseResolutionsData = $responseResolutions->getData();
    $this->assertInstanceOf('stdClass', $responseResolutionsData);
    $this->assertObjectHasAttribute('resolutions', $responseResolutionsData);
    $this->assertInstanceOf('stdClass', $responseResolutionsData->resolutions);
    $this->assertObjectHasAttribute('enabled', $responseResolutionsData->resolutions);
    $this->assertTrue($responseResolutionsData->resolutions->enabled);
    $this->assertObjectHasAttribute('data', $responseResolutionsData->resolutions);
    $this->assertEquals($params->resolutions, $responseResolutionsData->resolutions);

    $this->dispatch('website/getbyid/params/{"id":"' . $website->id . '"}');
    $responseWebsite = $this->getValidatedSuccessResponse();
    $responseWebsiteData = $responseWebsite->getData();
    $this->assertInstanceOf('stdClass', $responseWebsiteData);
    $this->assertObjectHasAttribute('resolutions', $responseWebsiteData);
    $this->assertInstanceOf('stdClass', $responseWebsiteData->resolutions);
    $this->assertObjectHasAttribute('enabled', $responseWebsiteData->resolutions);
    $this->assertTrue($responseWebsiteData->resolutions->enabled);
    $this->assertObjectHasAttribute('data', $responseWebsiteData->resolutions);
    $this->assertEquals($params->resolutions, $responseResolutionsData->resolutions);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidParamsProvider
   */
  public function invalidParams($invalidParams, $invalidKeys)
  {
    $this->dispatch('website/editresolutions/params/' . json_encode($invalidParams));
    $editResponse = $this->getValidatedErrorResponse();
    $errors = $editResponse->getError();

    $this->assertEquals(count($invalidKeys), count($errors), 'wrong error count: '.var_export($errors, true));
    foreach ($errors as $error) {
      $this->assertContains($error->param->field, $invalidKeys);
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider accessDeniedUserProvider
   */
  public function editResolutionsShouldReturnAccessDenied($websiteId, $username, $password, $userIswebsiteMember)
  {
    $this->activateGroupCheck();
    
    $this->assertSuccessfulLogin($username, $password);

    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';
    $newResolution = (object) array(
      'enabled' => true,
      'data'    => array(
        (object) array(
          'id'    => 'res1',
          'width' => 768,
          'name'  => 'Tablet',
        ),
      )
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
      'resolutions' => $newResolution,
    );
    $this->dispatch('website/editresolutions/params/' . json_encode($params));

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
  public function superuserEditResolutionsShouldReturnSuccess()
  {
    $params = array(
      'id'          => 'SITE-neg4e89c-22af-46cd-a651-fc42dc78fe50-SITE',
      'resolutions' => (object) array(
        'enabled' => true,
        'data'    => array(
          (object) array(
            'id'    => 'res1',
            'width' => 160,
            'name'  => 'Datenbrille',
          ),
        ),
      ),
    );

    $this->activateGroupCheck();

    // Superuser ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');

    $this->dispatch('website/editresolutions/params/' . json_encode($params));

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
        array(),
        array(
          'id',
        )
      ),
      array(
        array(
          'id' => array(),
          'resolutions' => 'INVALID RESOLUTIONS',
        ),
        array(
          'id', 'resolutions',
        ),
      ),
      array(
        array(
          'resolutions' => array(),
        ),
        array(
          'id', 'resolutions',
        ),
      ),
      array(
        array(
          'id' => '1',
          'resolutions' => '',
        ),
        array(
          'id', 'resolutions',
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
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
            'enabled'     => true,
            'data'        => new \stdClass(),
        )),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
            'data'        => array(),
        )),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
              'enabled'     => 'yes',
              'data'        => array(),
            )),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
              'enabled'     => true,
              'data'        => array(
                'name'        => 'Smartphone',
          ))),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
              'enabled'     => true,
              'data'        => array(
                'width'       => 480,
              ))),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
              'enabled'     => true,
              'data'        => array(
                'id'          => 'res1',
              ))),
        array('resolutions'),
      ),
      array(
        array(
          'id' => 'SITE-00000000-0000-0000-0000-000000000001-SITE',
          'resolutions' => (object)array(
              'enabled'     => true,
              'data'        => array(
                'name'        => 'Smartphone',
                'width'       => 480,
              ))),
        array('resolutions'),
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