<?php


namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService;
use Seitenbau\Registry;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase;


class WebsiteTestService extends WebsiteService
{
  private $phpunitTestCalls = array();
  private $phpunitTestWebsiteCount = 0;

  /**
   * @return array
   */
  public function restPhpunitTestCalls()
  {
    $this->phpunitTestCalls = array();
  }

  /**
   * @param int $count
   */
  public function setPhpunitTestWebsiteCount($count)
  {
    $this->phpunitTestWebsiteCount = $count;
  }

  /**
   * @return array
   */
  public function getPhpunitTestCalls()
  {
    return $this->phpunitTestCalls;
  }

  /**
   * overwrite execute method for testing
   */
  public function execute($function, array $arguments = array())
  {
    if ($function == 'getCount') {
      return $this->phpunitTestWebsiteCount;
    }
    return parent:: execute($function, $arguments);
  }

  /**
   * overwrite checkWebsiteMaxCountQuota method for testing
   */
  public function checkWebsiteMaxCountQuota()
  {
    $this->phpunitTestCalls[] = array(__METHOD__, time());
    parent::checkWebsiteMaxCountQuota();
  }
}


class WebsiteQuotaTest extends ServiceTestCase
{
  /**
   * @var WebsiteTestService
   */
  private $testService;

  protected function setUp()
  {
    parent::setUp();

    $this->testService = new WebsiteTestService('Website');

    // reset calls and set website count
    $this->testService->restPhpunitTestCalls();
    $this->testService->setPhpunitTestWebsiteCount(5);

    // set quota in config
    ConfigHelper::mergeIntoConfig(array('quota' => array('website' => array('maxCount' => 5))));
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2300
   */
  public function test_checkWebsiteMaxCountQuotaShouldThrowExceptionIfMaxCountReached()
  {
    // ARRANGE
    $service = new WebsiteService('Website');

    // ACT
    $service->checkWebsiteMaxCountQuota();
  }

  /**
   * @test
   * @group library
   */
  public function test_createWebsiteShouldThrowExceptionIfMaxCountReached()
  {
    // ARRANGE
    $attributes = array(
      'name' => 'PHPUnit Test Website Service - quota@create'
    );

    // ACT
    $e = null;
    try {
      $this->testService->create($attributes);
    } catch (\Exception $e) {
    }

    // ASSERT
    $this->assertQuotaException($e, 2300);
    $this->assertCount(1, $this->testService->getPhpunitTestCalls());
  }

  /**
   * @test
   * @group library
   */
  public function test_copyWebsiteShouldThrowExceptionIfMaxCountReached()
  {
    // ARRANGE
    $existingWebsiteId = 'SourceWebsiteId';
    $newName ='PHPUnit Test Website Service - quota@copy';

    // ACT
    $e = null;
    try {
      $this->testService->copy($existingWebsiteId, $newName);
    } catch (\Exception $e) {
    }

    // ASSERT
    $this->assertQuotaException($e, 2300);
    $this->assertCount(1, $this->testService->getPhpunitTestCalls());
  }

  /**
   * @param \Exception $e
   * @param int        $expectedExceptionCode
   */
  protected function assertQuotaException($e, $expectedExceptionCode)
  {
    if (!($e instanceof \Cms\Exception)) {
      $this->fail('Failed asserting that exception of type "\Cms\Exception" is thrown.');
    }
    if ($e->getCode() != $expectedExceptionCode) {
      $this->fail('Failed asserting that ' . $e->getCode() . ' is equal to expected exception code ' . $expectedExceptionCode . '.');
    }
  }
}
 