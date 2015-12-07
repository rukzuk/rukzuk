<?php
namespace Application\Controller\Uuid;

use Cms\Validator\UniqueId as UniqueIdValidator,    
    Orm\Data\Page as DataPage,
    Orm\Data\Modul as DataModul,
    Orm\Data\Site as DataSite,
    Orm\Data\Unit as DataUnit,
    Orm\Data\Template as DataTemplate,
    Seitenbau\Registry as Registry,
    Test\Seitenbau\Cms\Response as CmsResponse,
    Test\Seitenbau\ControllerTestCase as NonModuleAwareTestcase;

/**
 * UuidControllerTest
 *
 * @package      Application
 * @subpackage   Controller
 */
class UuidControllerTest extends NonModuleAwareTestcase
{
  protected $responseCode = 200;

  /**
   * @test
   * @group integration
   * @dataProvider uuidProvider
   * @param string  $action
   * @param string  $resource
   * @param integer $count
   */
  public function getUuidShouldReturnFiveValidUuidsForUuidProvider(
    $action, $resource, $count)
  {
    $dispatchUri = sprintf(
      '/uuid/%s/params/{"count":%d}',
      $action,
      $count
    );

    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());
    $data = $cmsResponse->getData();
    $this->assertObjectHasAttribute('uuids', $data);
    $this->assertInternalType('array', $data->uuids);
    $uuids = $data->uuids;
    $this->assertTrue(count($uuids) === $count);

    $dataResource = sprintf('Orm\Data\%s', $resource);

    $uniqueidValidator = new UniqueIdValidator(
      $dataResource::ID_PREFIX,
      $dataResource::ID_SUFFIX
    );

    foreach ($uuids as $uuid) 
    {
      $this->assertTrue($uniqueidValidator->isValid($uuid));
    }
    $this->resetResponse();
    $this->resetRequest();
  }

  /**
   * @return array
   */
  public function uuidProvider()
  {
    return array(
      array('getpageids', 'Page', 5),
      array('getmoduleids', 'Modul', 5),
      array('gettemplateids', 'Template', 5),
      array('getunitids', 'Unit', 5),
      array('getsiteids', 'Site', 5),
      // Wegen Fehler in der Class Zend_Validate_GreaterThan auf count:1 pruefen
      array('getunitids', 'Unit', 1),
    );
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getPageIdsShouldReturnHttpResponseCode400ForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getpageids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $this->assertResponseCode($this->responseCode);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getPageIdsShouldReturnNonSuccessfulCmsResponseForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getpageids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());
    $this->assertFalse($cmsResponse->getSuccess());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getModulIdsShouldReturnResponseCode400ForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getmoduleids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $this->assertResponseCode($this->responseCode);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getModulIdsShouldReturnNonSuccessfulCmsResponseForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getmoduleids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());
    $this->assertFalse($cmsResponse->getSuccess());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getTemplateIdsShouldReturnResponseCode400ForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/gettemplateids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $this->assertResponseCode($this->responseCode);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getTemplateIdsShouldReturnNonSuccessfulCmsResponseForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/gettemplateids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());
    $this->assertFalse($cmsResponse->getSuccess());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getSiteIdsShouldReturnResponseCode400ForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getsiteids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $this->assertResponseCode($this->responseCode);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getSiteIdsShouldReturnNonSuccessfulCmsResponseForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getsiteids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());
    $this->assertFalse($cmsResponse->getSuccess());
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getUnitIdsShouldReturnResponseCode400ForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getunitids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $this->assertResponseCode($this->responseCode);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidCountValuesProvider
   */
  public function getUnitIdsShouldReturnNonSuccessfulCmsResponseForInvalidCountValues($count)
  {
    $dispatchUri = sprintf(
      '/uuid/getunitids/params/{"count":%s}',
      $count
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());

    $this->assertFalse($cmsResponse->getSuccess());
  }

  /**
   * @test
   * @group integration
   */
  public function getUnitIdsShouldReturnNonSuccessfulCmsResponseForCountValueGreaterThanConfiguredLimit()
  {
    $config = Registry::getConfig();
    $configuredUuidLimit = intval($config->uuid->limit);
    $exceedingUuidLimit = $configuredUuidLimit + 1;

    $dispatchUri = sprintf(
      '/uuid/getunitids/params/{"count":%d}',
      $exceedingUuidLimit
    );
    $this->dispatch($dispatchUri);
    $cmsResponse = new CmsResponse($this->getResponseBody());

    $this->assertFalse($cmsResponse->getSuccess());
  }

  /**
   * @return array
   */
  public function invalidCountValuesProvider()
  {
    return array(
      array(0),
      array('0'),
      array('0.25'),
      array(null),
      array('test'),
      array(0.78),
    );
  }
}