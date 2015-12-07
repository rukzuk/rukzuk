<?php


namespace Cms\Service\Website;

use Cms\Service\Website as WebsiteService;
use Cms\Service\Website;
use Seitenbau\Registry;
use Test\Rukzuk\ConfigHelper;
use Test\Seitenbau\ServiceTestCase;


class WebhostingQuotaTestWebsiteService extends WebsiteService
{
  private $phpunitTestCalls = array();
  private $phpunitTest_daoMethodReturn = array();

  /**
   * @return array
   */
  public function resetPhpunitTestCalls()
  {
    $this->phpunitTestCalls = array();
  }

  /**
   * @return array
   */
  public function getPhpunitTestCalls()
  {
    return $this->phpunitTestCalls;
  }

  /**
   * @param $methodName
   * @param $returnValue
   */
  public function setPhpunitTest_daoMethodReturn($methodName, $returnValue)
  {
    $this->phpunitTest_daoMethodReturn[$methodName][] = $returnValue;
  }

  /**
   * @param string $methodName
   * @param array  $arguments
   *
   * @return mixed
   */
  public function execute($methodName, array $arguments = array())
  {
    if (array_key_exists($methodName, $this->phpunitTest_daoMethodReturn)) {
      return array_shift($this->phpunitTest_daoMethodReturn[$methodName]);
    }
    return parent::execute($methodName, $arguments);
  }

  /**
   * @param array $attributes
   * @param null  $websiteId
   */
  public function checkWebhostingMaxCountQuota(array $attributes, $websiteId = null)
  {
    $this->phpunitTestCalls[] = array(__METHOD__, time(), array(
      $attributes, $websiteId));
    parent::checkWebhostingMaxCountQuota($attributes, $websiteId);
  }
}


class WebhostingQuotaTest extends ServiceTestCase
{
  /**
   * @var WebhostingQuotaTestWebsiteService
   */
  private $testService;

  private $testWebhostingMaxCount = 3;

  protected function setUp()
  {
    parent::setUp();

    $this->testService = new WebhostingQuotaTestWebsiteService('Website');

    // reset calls and set website count
    $this->testService->resetPhpunitTestCalls();

    // set webhosting max count quota
    ConfigHelper::mergeIntoConfig(array('quota' => array('webhosting' => array(
      'maxCount' => $this->testWebhostingMaxCount))));
  }

  /**
   * @test
   * @group library
   * @expectedException \Cms\Exception
   * @expectedExceptionCode 2303
   */
  public function test_checkWebhostingMaxCountQuotaShouldThrowExceptionIfMaxCountReached()
  {
    // ARRANGE
    $websites = $this->createTestWebsites(($this->testWebhostingMaxCount + 1),
      $this->testWebhostingMaxCount);
    $this->testService->setPhpunitTest_daoMethodReturn('getAll', $websites);

    // ACT
    $this->testService->checkWebhostingMaxCountQuota(array('publishingenabled' => true));
  }

  /**
   * @test
   * @group library
   */
  public function test_checkWebhostingMaxCountQuotaShouldntThrowExceptionIfMaxCountNotReached()
  {
    // ARRANGE
    $websites = $this->createTestWebsites(($this->testWebhostingMaxCount - 1),
      $this->testWebhostingMaxCount);
    $this->testService->setPhpunitTest_daoMethodReturn('getAll', $websites);

    // ACT
    $this->testService->checkWebhostingMaxCountQuota(array('publishingenabled' => true));

    // ASSERT
    $this->assertCount(1, $this->testService->getPhpunitTestCalls());
  }

  /**
   * @test
   * @group library
   */
  public function test_createWebsiteShouldThrowExceptionIfWebhostingMaxCountReached()
  {
    // ARRANGE
    $attributes = array(
      'name' => 'PHPUnit Test Website Service - webhosting_max_count_quota@create',
      'publishingenabled' => true,
    );
    $websites = $this->createTestWebsites($this->testWebhostingMaxCount, 2);
    $this->testService->setPhpunitTest_daoMethodReturn('getAll', $websites);

    // ACT
    $e = null;
    try {
      $this->testService->create($attributes);
    } catch (\Exception $e) {
    }

    // ASSERT
    $this->assertQuotaException($e, 2303);
    $this->assertCount(1, $this->testService->getPhpunitTestCalls());
  }

  /**
   * @test
   * @group library
   */
  public function test_updateWebsiteShouldThrowExceptionIfMaxCountReached()
  {
    // ARRANGE
    $websiteId = 'WEBSITE-ID-FROM-WEBSITE-WITH-PUBLISHING-DISABLED';
    $attributes = array(
      'name' => 'PHPUnit Test Website Service - webhosting_max_count_quota@create',
      'publishingenabled' => true,
    );
    $websites = $this->createTestWebsites($this->testWebhostingMaxCount, 2);
    $this->testService->setPhpunitTest_daoMethodReturn('getAll', $websites);

    // ACT
    $e = null;
    try {
      $this->testService->update($websiteId, $attributes);
    } catch (\Exception $e) {
    }

    // ASSERT
    $this->assertQuotaException($e, 2303);
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

  /**
   * @param $websiteCountWithPublishingEnabled
   * @param $websiteCountWithPublishingDisabled
   *
   * @return \Cms\Data\Website
   */
  private function createTestWebsites($websiteCountWithPublishingEnabled,
                               $websiteCountWithPublishingDisabled)
  {
    $publishingEnabledArray = array();
    for ($i=1; $i<=$websiteCountWithPublishingEnabled; $i++) {
      $publishingEnabledArray[] = true;
    }
    for ($i=1; $i<=$websiteCountWithPublishingDisabled; $i++) {
      $publishingEnabledArray[] = false;
    }
    shuffle($publishingEnabledArray);

    $websites = array();
    foreach ($publishingEnabledArray as $i => $isEnabled) {
      $website = new \Cms\Data\Website();
      $website->setId('WEBSITE-ID-'.$i);
      $website->setName('publishingEnabled: '.($isEnabled ? 'yes' : 'no'));
      $website->setPublishingEnabled($isEnabled);
      $websites[] = $website;
    }
    return $websites;
  }
}
 