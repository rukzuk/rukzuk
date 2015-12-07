<?php
namespace Application\Controller\Media;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;
/**
 * MediaController GetSearchFilterTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetSearchFilterTest extends ControllerTestCase
{
  /**
   * @test
   * @group integration
   * @dataProvider invalidSearchFilterProvider
   */
  public function getShouldFailOnInvalidSearchFilter($invalidSearchFilter)
  {
    $websiteId = 'SITE-ra10e89c-22af-46sf-a651-fc42dc78fe50-SITE';
    $requestUri = sprintf(
      '/media/get/params/{"websiteid":"%s","search":"%s"}',
      $websiteId,
      $invalidSearchFilter
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $this->assertEmpty($response->getData());
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidSearchFilterProvider
   */
  public function getWithSearchFilterShouldOnlyReturnMatchingMediasAndExpectedTotal()
  {
    $websiteId = 'SITE-ra10e89c-22af-46sf-a651-fc42dc78fe50-SITE';
    $searchFilter = 'Test_Data_Search_Filter';

    $requestUri = sprintf(
      '/media/get/params/{"websiteid":"%s","search":"%s"}',
      $websiteId,
      $searchFilter
    );
    $this->dispatch($requestUri);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $this->assertNotEmpty($response->getData());

    $responseData = $response->getData();

    $this->assertObjectHasAttribute('media', $responseData);
    $this->assertObjectHasAttribute('total', $responseData);
    $mediaItems = $responseData->media;
    $this->assertInternalType('array', $mediaItems);

    $this->assertTrue(count($mediaItems) == 2);

    $this->assertTrue($responseData->total == 2);
  }

  /**
   * @return array
   */
  public function invalidSearchFilterProvider()
  {
    $tooLongString = str_repeat('toolsearchfilter', 24);
    return array(
      array(''),
      array(null),
      array($tooLongString)
    );
  }
}