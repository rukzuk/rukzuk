<?php


namespace Cms;


use Cms\Quota;
use Seitenbau\Registry;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

class QuotaTest extends AbstractTestCase
{
  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getQuotaAsArraySuccess()
  {
    // ARRANGE
    $expectedQuota = array(
      'media' => array(
        'maxFileSize' => (1024 * 1024 * 50), // 50MB
        'maxSizePerWebsite' => (1024 * 1024 * 1024 * 3), // 3GB
      ),
      'website' => array(
        'maxCount' => 99999999,
      ),
      'webhosting' => array(
        'maxCount' => 99999999,
      ),
      'export' => array(
        'exportAllowed' => true,
      ),
      'module' => array(
        'enableDev' => true,
      ),
      'expired' => false,
    );

    // ACT
    $quota = new Quota();
    $actualQuota = $quota->toArray();

    // ASSERT
    $this->assertEquals($expectedQuota, $actualQuota);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getWebsiteQuotaSuccess()
  {
    // ARRANGE
    $expectedWebsiteMaxCount = 123;
    ConfigHelper::mergeIntoConfig(array('quota' => array(
      'website' => array('maxCount' => $expectedWebsiteMaxCount))));

    // ACT
    $quota = new Quota();
    $actualWebsiteQuota = $quota->getWebsiteQuota();

    // ASSERT
    $actualWebsiteMaxCount = $actualWebsiteQuota->getMaxCount();
    $this->assertEquals($expectedWebsiteMaxCount, $actualWebsiteMaxCount);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getDefaultWebsiteQuotaSuccess()
  {
    // ARRANGE
    $expectedWebsiteMaxCount = 0;
    ConfigHelper::removeValue(array('quota'));

    // ACT
    $quota = new Quota();
    $actualWebsiteQuota = $quota->getWebsiteQuota();

    // ASSERT
    $actualWebsiteMaxCount = $actualWebsiteQuota->getMaxCount();
    $this->assertEquals($expectedWebsiteMaxCount, $actualWebsiteMaxCount);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getWebhostingQuotaSuccess()
  {
    // ARRANGE
    $expectedWebhostingMaxCount = 123;
    ConfigHelper::mergeIntoConfig(array('quota' => array(
      'webhosting' => array('maxCount' => $expectedWebhostingMaxCount))));

    // ACT
    $quota = new Quota();
    $actualWehostingQuota = $quota->getWebhostingQuota();

    // ASSERT
    $actualWebsiteMaxCount = $actualWehostingQuota->getMaxCount();
    $this->assertEquals($expectedWebhostingMaxCount, $actualWebsiteMaxCount);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getDefaultWebhostingQuotaSuccess()
  {
    // ARRANGE
    $expectedWebhostingMaxCount = 0;
    ConfigHelper::removeValue(array('quota'));

    // ACT
    $quota = new Quota();
    $actualWebhostingQuota = $quota->getWebhostingQuota();

    // ASSERT
    $actualWebhostingMaxCount = $actualWebhostingQuota->getMaxCount();
    $this->assertEquals($expectedWebhostingMaxCount, $actualWebhostingMaxCount);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getExportQuotaSuccess()
  {
    // ARRANGE
    $expectedExportAllowed = true;
    ConfigHelper::mergeIntoConfig(array('quota' => array(
      'exportAllowed' => $expectedExportAllowed)));

    // ACT
    $quota = new Quota();
    $actualExportQuota = $quota->getExportQuota();

    // ASSERT
    $actualExportAllowed = $actualExportQuota->getExportAllowed();
    $this->assertEquals($expectedExportAllowed, $actualExportAllowed);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getDefaultExportQuotaSuccess()
  {
    // ARRANGE
    $expectedExportAllowed = false;
    ConfigHelper::removeValue(array('quota'));

    // ACT
    $quota = new Quota();
    $actualExportQuota = $quota->getExportQuota();

    // ASSERT
    $actualExportAllowed = $actualExportQuota->getExportAllowed();
    $this->assertEquals($expectedExportAllowed, $actualExportAllowed);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getModuleQuotaSuccess()
  {
    // ARRANGE
    $expectedEnableDev = true;
    ConfigHelper::mergeIntoConfig(array('quota' => array(
      'module' => array('enableDev' => $expectedEnableDev))));

    // ACT
    $quota = new Quota();
    $actualModuleQuota = $quota->getModuleQuota();

    // ASSERT
    $actualEnableDev = $actualModuleQuota->getEnableDev();
    $this->assertEquals($expectedEnableDev, $actualEnableDev);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getDefaultModuleQuotaSuccess()
  {
    // ARRANGE
    $expectedEnableDev = false;
    ConfigHelper::removeValue(array('quota'));

    // ACT
    $quota = new Quota();
    $actualModuleQuota = $quota->getModuleQuota();

    // ASSERT
    $actualEnableDev = $actualModuleQuota->getEnableDev();
    $this->assertEquals($expectedEnableDev, $actualEnableDev);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getMediaQuotaSuccess()
  {
    // ARRANGE
    $expectedMaxFileSize = 12345;
    $expectedMaxSizePerWebsite = 67890;
    ConfigHelper::mergeIntoConfig(array('quota' => array('media' => array(
      'maxFileSize' => $expectedMaxFileSize,
      'maxSizePerWebsite' => $expectedMaxSizePerWebsite,
    ))));

    // ACT
    $quota = new Quota();
    $actualMediaQuota = $quota->getMediaQuota();

    // ASSERT
    $actualMaxFileSize = $actualMediaQuota->getMaxFileSize();
    $this->assertEquals($expectedMaxFileSize, $actualMaxFileSize);
    $actualMaxSizePerWebsite = $actualMediaQuota->getMaxSizePerWebsite();
    $this->assertEquals($expectedMaxSizePerWebsite, $actualMaxSizePerWebsite);
  }

  /**
   * @test
   *
   * @group quota
   * @group small
   * @group dev
   */
  public function test_getDefaultMediaQuotaSuccess()
  {
    // ARRANGE
    $expectedMaxFileSize = 0;
    $expectedMaxSizePerWebsite = 0;
    ConfigHelper::removeValue(array('quota'));

    // ACT
    $quota = new Quota();
    $actualMediaQuota = $quota->getMediaQuota();

    // ASSERT
    $actualMaxFileSize = $actualMediaQuota->getMaxFileSize();
    $this->assertEquals($expectedMaxFileSize, $actualMaxFileSize);
    $actualMaxSizePerWebsite = $actualMediaQuota->getMaxSizePerWebsite();
    $this->assertEquals($expectedMaxSizePerWebsite, $actualMaxSizePerWebsite);
  }
}
 