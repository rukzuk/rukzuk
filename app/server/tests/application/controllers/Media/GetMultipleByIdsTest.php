<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase;

/**
 * MediaController GetByIdTest
 *
 * @package      Test
 * @subpackage   Controller
 */

class GetMultipleByIdsTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   */
  public function successWithExistsAndNonExistsMediaIds()
  {
    $websiteId = 'SITE-779ca2e0-7948-4bd6-958f-8a92e287fe22-SITE';
    $existsMediaIds = array(
        'MDB-e2611218-3590-4cdf-b7bc-4d59ed4c88aa-MDB', 
        'MDB-dca4f746-c420-407f-b145-7de175d2bb09-MDB'
    );
    $nonExistsMediaIds = array(
        'MDB-366869ef-6e4a-4646-b44c-5853a6cc994f-MDB', 
        'MDB-0733a4c1-1bef-4cc5-9ab3-67103e161984-MDB'
    );
    $mediaIds = array_merge($existsMediaIds, $nonExistsMediaIds);
    
    $requestUri = sprintf(
      '/media/getmultiplebyids/params/{"websiteid":"%s","ids":["%s"]}',
      $websiteId, implode('","',$mediaIds));
    ;
    
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
        
    $this->assertObjectHasAttribute('media', $responseObject->data);
    $this->assertInstanceOf('stdClass', $responseObject->data->media);
    $responseMediaItems = get_object_vars($responseObject->data->media);
    $this->assertSame(count($mediaIds), count($responseMediaItems));

    foreach ($responseMediaItems as $responseMediaId => $responseMedia)
    {
      if (in_array($responseMediaId, $existsMediaIds))
      {
        $this->assertInstanceOf('stdClass', $responseMedia);
        $this->assertObjectHasAttribute('id', $responseMedia);
        unset($existsMediaIds[array_search($responseMediaId, $existsMediaIds)]);
      }
      elseif (in_array($responseMediaId, $nonExistsMediaIds))
      {
        $this->assertFalse($responseMedia);
        unset($nonExistsMediaIds[array_search($responseMediaId, $nonExistsMediaIds)]);
      }
      else
      {
        $this->fail('IDs der Response-Media-Items muessen explizit angefordert werden');
      }
    }
    
    // Alle Media-Items wurden erfolgreich im Response aufgezaehlt
    $this->assertSame(0, count($existsMediaIds));
    $this->assertSame(0, count($nonExistsMediaIds));
  }
 
  /**
   * @test
   * @group integration
   */
  public function successWithOnlyNonExistsMediaIds()
  {
    $websiteId = 'SITE-779ca2e0-7948-4bd6-958f-8a92e287fe22-SITE';
    $nonExistsMediaIds = array(
        'MDB-0b4247e9-048d-43fc-81d8-4024a772090b-MDB', 
        'MDB-b7160049-365c-43b1-b26c-20ca88fbdb81-MDB'
    );
    $requestUri = sprintf(
      '/media/getmultiplebyids/params/{"websiteid":"%s","ids":["%s"]}',
      $websiteId, implode('","',$nonExistsMediaIds));
    ;
    
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);
    
    // Response allgemein pruefen
    $this->assertResponseBodySuccess($responseObject);
    
    $this->assertObjectHasAttribute('media', $responseObject->data);
    $this->assertInstanceOf('stdClass', $responseObject->data->media);
    $responseMediaItems = get_object_vars($responseObject->data->media);
    $this->assertSame(count($nonExistsMediaIds), count($responseMediaItems));
    
    foreach ($responseMediaItems as $responseMediaId => $responseMedia)
    {
      if (in_array($responseMediaId, $nonExistsMediaIds))
      {
        $this->assertFalse($responseMedia);
        unset($nonExistsMediaIds[array_search($responseMediaId, $nonExistsMediaIds)]);
      }
      else
      {
        $this->fail('IDs der Response-Media-Items muessen explizit angefordert werden');
      }
    }
    
    // Alle Media-Items wurden erfolgreich im Response aufgezaehlt
    $this->assertSame(0, count($nonExistsMediaIds));
  }
  
  /**
   * @test
   * @group integration
   */
  public function invalidParamsForRequest()
  {
    $params = array('ids' => 'UNGUELTIGE_ID', 'websiteid' => 'UNGUELTIGE_ID');
    $paramsAsJson = json_encode($params);
    $url = '/media/getmultiplebyids/params/' . $paramsAsJson;
    $this->dispatch('/media/getmultiplebyids/params/' . $paramsAsJson);
    $response = $this->getResponseBody();
    
    $this->assertInternalType('string', $response);
    $this->assertNotNull($response);
    $responseObject = json_decode($response);

    // Response allgemein pruefen
    $this->assertResponseBodyError($responseObject);

    foreach ($responseObject->error as $error)
    {
      $this->assertArrayHasKey($error->param->field, $params);
    }
  }
}