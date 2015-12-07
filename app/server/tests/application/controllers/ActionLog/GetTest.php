<?php
namespace Application\Controller\ActionLog;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ActionlogControllerTestCase as ActionlogControllerTestCase,
    Test\Seitenbau\Logger\ActionMock as ActionLoggerMock,
    Test\Seitenbau\Cms\Dao\MockManager as MockManager,
    Test\Seitenbau\Cms\Dao\Module\WriteableMock as ModuleWriteableMock,
    Cms\Business\ActionLog as ActionLogBusiness;

/**
 * GetTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class GetTest extends ActionlogControllerTestCase
{
  protected $sqlFixtures = array('application_controller_ActionLog_GetTest.json');

  protected function setUp()
  {
    MockManager::activateWebsiteSettingsMock(true);

    parent::setUp();

    ModuleWriteableMock::setUp();
    MockManager::setDaoCreate('Modul', function($daoName, $daoType) {
      return new ModuleWriteableMock();
    });
  }

  public function tearDown()
  {
    ModuleWriteableMock::tearDown();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldBeAllowedWhenAuthenticatedUserIsSuperuser()
  {
    $websiteId = 'SITE-1964e89c-0000-gelu-a651-fc42dc78fe50-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $userlogin = 'get.log.superuser@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $expectedLogEntryCount = 2;
    $this->assertSame($expectedLogEntryCount, count($responseData));
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldBeRejectedWhenAuthenticatedUserHasNoReadlogPrivilege()
  {
    $websiteId = 'SITE-1964e89c-0001-gelu-a651-fc42dc78fe50-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $userlogin = 'get.log.no.read.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->activateGroupCheck();

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame(7, $responseError[0]->code);
    $this->assertNull($response->getData());
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldBeReadableWhenAuthenticatedUserHasReadlogPrivilege()
  {
    $websiteId = 'SITE-1964e89c-0002-gelu-a651-fc42dc78fe50-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $userlogin = 'get.log.read.privileges@sbcms.de';
    $userPassword = 'TEST09';
    $this->assertSuccessfulLogin($userlogin, $userPassword);

    $this->alterActionLogLifetime(180);
    $this->activateGroupCheck();

    $this->dispatch($request);

    $this->alterActionLogLifetime(1);
    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();

    $response = new Response($response);

    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();
    $expectedLogEntryCount = 2;
    $this->assertInternalType('array', $responseData);
    $this->assertSame($expectedLogEntryCount, count($responseData));
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdProvider
   */
  public function getLogShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s"}',
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('websiteid', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidFormatProvider
   */
  public function getLogShouldReturnValidationErrorForInvalidFormats($format)
  {
    $websiteId = 'SITE-gl00eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"%s"}',
      $websiteId,
      $format
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('format', $errorData[0]->param->field);
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidLimitProvider
   */
  public function getLogShouldReturnValidationErrorForInvalidLimits($limit)
  {
    $websiteId = 'SITE-gl00eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","limit":%d}',
      $websiteId,
      $limit
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());
    $errorData = $response->getError();
    $this->assertSame('limit', $errorData[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function getLogShouldDeleteEntriesBelowConfiguredLifetimeBoundary()
  {
    $runId = 'CMSRUNID-00000000-0000-0000-0000-000000000001-CMSRUNID';

    $websiteIds = array(
      'SITE-1964e89c-0000-gelo-a651-fc42dc78fe50-SITE',
      'SITE-1964e89c-0001-gelo-a651-fc42dc78fe50-SITE',
      'SITE-1964e89c-0002-gelo-a651-fc42dc78fe50-SITE',
      'SITE-1964e89c-0003-gelo-a651-fc42dc78fe50-SITE',
      'SITE-1964e89c-0004-gelo-a651-fc42dc78fe50-SITE',
    );

    $this->deleteMultipleWebsites($runId, $websiteIds);

    foreach ($websiteIds as $websiteId) {
      $request = sprintf(
        '/log/get/params/{"websiteId":"%s","format":"json"}',
        $websiteId
      );

      $this->dispatch($request);
      $response = $this->getResponseBody();

      $response = new Response($response);
      $this->assertTrue($response->getSuccess());

      $responseData = $response->getData();

      $this->assertSame(1, count($responseData));

      $this->dispatch($request);
      $response = $this->getResponseBody();

      $response = new Response($response);
      $this->assertTrue($response->getSuccess());

      $responseData = $response->getData();

      $this->assertEmpty($responseData);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldReturnOnlyLogEntriesOfGivenWebsite()
  {
    $websiteId = 'SITE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $expectedLogEntries = 4;
    $this->assertSame($expectedLogEntries, count($responseData));

    $expecteActionLogIds = array(
     'PAGE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-PAGE',
     'PAGE-gl02eaa7-8fc5-464a-bd47-06b3b8af00dg-PAGE',
     'PAGE-gl02eaa7-9fc5-464a-bd47-06b3b8af00dg-PAGE',
     'PAGE-gl02eaa7-0fc5-464a-bd47-06b3b8af00dg-PAGE',
    );

    $this->assertInternalType('array', $responseData);
    foreach ($responseData as $logEntry)
    {
      $this->assertContains($logEntry->id, $expecteActionLogIds);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldReturnLogEntriesDesecendingByTimestamp()
  {
    $websiteId = 'SITE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $expectedLogEntries = 4;
    $this->assertSame($expectedLogEntries, count($responseData));

    foreach ($responseData as $index =>  $logEntry)
    {
      if (isset($responseData[$index + 1]))
      {
        $this->assertGreaterThan(
          strtotime($responseData[$index + 1]->dateTime),
          strtotime($logEntry->dateTime)
        );
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldReturnExpectedStructureForJsonFormat()
  {
    $websiteId = 'SITE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $expectedLogEntries = 4;
    $this->assertInternalType('array', $responseData);
    $this->assertSame($expectedLogEntries, count($responseData));

    $expectedLogEntryKeys = array(
      'id', 'name', 'dateTime', 'userlogin', 'action', 'additionalinfo'
    );
    sort($expectedLogEntryKeys);

    foreach ($responseData as $logEntry)
    {
      $this->assertInstanceOf('stdClass', $logEntry);
      $actualLogEntryKeys = array_keys(get_object_vars($logEntry));
      sort($actualLogEntryKeys);
      $this->assertSame($expectedLogEntryKeys, $actualLogEntryKeys);
    }
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldReturnExpectedStructureForTxtFormat()
  {
    $websiteId = 'SITE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","limit":1}',
      $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $this->assertInternalType('array', $responseData);
    $this->assertSame(1, count($responseData));

    $expectedLogEntryData = array(
      'PAGE-gl02eaa7-0fc5-464a-bd47-06b3b8af00dg-PAGE',
      'Page_Name_3',
      '29.07.2011 13:26:04',
      'foo@sbcms.de',
      'Page_Aktion_3'
    );

    $actualLogEntryData = explode(
      ActionLogBusiness::TEXT_DELIMITER,
      $responseData[0]
    );

    $this->assertSame(count($expectedLogEntryData), count($actualLogEntryData));
    $this->assertSame($expectedLogEntryData, $actualLogEntryData);
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldNotApplyLimitOnDefault()
  {
    $websiteId = 'SITE-gl02eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","format":"json"}',
      $websiteId
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $expectedLogEntries = 4;
    $this->assertInternalType('array', $responseData);
    $this->assertSame($expectedLogEntries, count($responseData));
  }

  /**
   * @test
   * @group integration
   */
  public function getLogShouldApplyLimit()
  {
    $websiteId = 'SITE-gl01eaa7-7fc5-464a-bd47-06b3b8af00dg-SITE';
    $limit = 3;
    $request = sprintf(
      '/log/get/params/{"websiteId":"%s","limit":%d, "format":"json"}',
      $websiteId,
      $limit
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $expectedLogEntries = $limit;
    $this->assertInternalType('array', $responseData);
    $this->assertSame($expectedLogEntries, count($responseData));
  }

  /**
   * @param array $websiteIds
   */
  private function deleteMultipleWebsites($runId, array $websiteIds)
  {
    if (count($websiteIds) > 0)
    {
      foreach ($websiteIds as $index => $websiteId)
      {
        $params = array('id' => $websiteId, 'runId' => $runId);
        $paramsAsJson = json_encode($params);

        $userlogin = 'log.website@sbcms.de';
        $userPassword = 'TEST09';
        $this->assertSuccessfulLogin($userlogin, $userPassword);

        $this->activateGroupCheck();

        $seconds = $index + 2;
        $twoDaysAgo = strtotime("-2 days -$seconds seconds");

        ActionLoggerMock::setCurrentTimestamp($twoDaysAgo);

        $this->dispatch('website/delete/params/' . $paramsAsJson);

        $this->deactivateGroupCheck();

        $responseBody = $this->getResponseBody();

        $response = new Response($responseBody);

        $this->assertTrue($response->getSuccess(), $responseBody);
        usleep(5);
      }
    }
    ActionLoggerMock::clearCurrentTimestamp();
  }

  /**
   * @return array
   */
  public function invalidWebsiteIdProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }

  /**
   * @return array
   */
  public function invalidFormatProvider()
  {
    return array(
      array(null),
      array('xml'),
      array('15'),
      array('js'),
    );
  }

  /**
   * @return array
   */
  public function invalidLimitProvider()
  {
    return array(
      array(0),
      array('abc'),
      array(null),
    );
  }
}