<?php
namespace Application\Controller\Website;

use Test\Seitenbau\ControllerTestCase;

/**
 * WebsiteController Create Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class CreateTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function successCreateWebsite()
  {
    $params = array(
      'name' => 'test create success',
      'publishEnabled' => true, // should not be used at creating
      'publish' => array(
        'type' => 'internal',
        'cname' => 'my.domain.tld',
      ),
      'colorscheme' => array(
        array(
          'id' => '1',
          'name' => 'farbe1',
          'value' => 'rgba(255,255,255,1)'
        ),
        array(
          'id' => '2',
          'name' => 'farbe2',
          'value' => 'rgba(200,255,255,1)'
        )
      ),
      'resolutions' => array(
        'enabled' => true,
        'data'    => array(),
      ),
    );
    $paramsAsJson = json_encode($params);
    
    $this->dispatch('website/create/params/' . $paramsAsJson);

    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);

    // neue Website-ID muss zurueckgegeben werden
    $this->assertNotNull($responseObject->data);
    $this->assertNotNull($responseObject->data->id);
    $newWebsiteId = $responseObject->data->id;

    // Werte der neuen Website pruefen
    $this->dispatch('website/getbyid/params/{"id":"' . $newWebsiteId . '"}');
    $response = $this->getResponseBody();
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    // Werte pruefen
    $this->assertSame($newWebsiteId, $responseObject->data->id);
    $this->assertSame($params['name'], $responseObject->data->name);
    $this->assertSame(0, $responseObject->data->version);
    $this->assertSame(json_encode($params['resolutions']), json_encode($responseObject->data->resolutions));
    $this->assertObjectHasAttribute('publishingEnabled', $responseObject->data);
    $this->assertFalse($responseObject->data->publishingEnabled);
    $this->assertObjectHasAttribute('publish', $responseObject->data);
    $this->assertInstanceOf('stdClass', $responseObject->data->publish);
    $this->assertObjectHasAttribute('type', $responseObject->data->publish);
    $this->assertEquals($params['publish']['type'], $responseObject->data->publish->type);
    $this->assertObjectHasAttribute('cname', $responseObject->data->publish);
    $this->assertEquals($params['publish']['cname'], $responseObject->data->publish->cname);

    foreach ($params['colorscheme'] as $key => $expectedColorscheme)
    {
      $this->assertArrayHasKey($key, $responseObject->data->colorscheme);
      $responseColorscheme = $responseObject->data->colorscheme[$key];
      $this->assertSame($expectedColorscheme['id'], $responseColorscheme->id);
      $this->assertSame($expectedColorscheme['name'], $responseColorscheme->name);
      $this->assertSame($expectedColorscheme['value'], $responseColorscheme->value);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function noParams()
  {
    $this->dispatch('website/create/');

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
    $this->assertArrayHasKey('name', $invalidKeys);
  }

  /**
   * @test
   * @group integration
   */
  public function invalidParams()
  {
    $this->dispatch('website/create/params/{"name":"ab","colorscheme":[{"id":"1","value":"rgba(255,255,255,1)","name":"farbe1","invalidKey":"value"},{"id":"2","value":"rgba(255,255,255,1)","name":"farbe2"}]}');

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
    $this->assertArrayHasKey('colorscheme', $invalidKeys);
    $this->assertArrayHasKey('name', $invalidKeys);
  }

  /**
   * User darf keine Website erstellen
   *
   * @test
   * @group integration
   */
  public function createShouldReturnAccessDenied()
  {
    $params = array('name' => 'new website');
    $paramsAsJson = json_encode($params);
    $request = 'website/create/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('get.all.privileges@sbcms.de', 'TEST09');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * Super-User darf Websites erstellen
   *
   * @test
   * @group integration
   */
  public function superuserCreateShouldReturnSuccess()
  {
    $params = array('name' => 'new website');
    $paramsAsJson = json_encode($params);
    $request = 'website/create/params/' . $paramsAsJson;

    $this->activateGroupCheck();

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }
}