<?php
namespace Cms;

use Seitenbau\Registry;
use Cms\Data\ModuleQuota;
use Cms\Data\WebsiteQuota;
use Cms\Data\WebhostingQuota;
use Cms\Data\ExportQuota;
use Cms\Data\MediaQuota;
use Cms\Version as CmsVersion;

/**
 * Class Quota
 * Creates the quota objects.
 * @package Cms\Access
 */
class Quota
{
  /**
   * Website related quotas
   * @return \Cms\Data\WebsiteQuota
   */
  public function getWebsiteQuota()
  {
    $config = Registry::getConfig();

    // get values
    if (isset($config->quota->website->maxCount)) {
      $maxCount = $config->quota->website->maxCount;
    } else {
      $maxCount = null;
    }

    return new WebsiteQuota($maxCount);
  }

  /**
   * Webhosting related quotas
   * @return \Cms\Data\WebhostingQuota
   */
  public function getWebhostingQuota()
  {
    $config = Registry::getConfig();

    // get values
    if (isset($config->quota->webhosting->maxCount)) {
      $maxCount = $config->quota->webhosting->maxCount;
    } else {
      $maxCount = null;
    }

    return new WebhostingQuota($maxCount);
  }

  /**
   * Export related quotas
   * @return \Cms\Data\ExportQuota
   */
  public function getExportQuota()
  {
    $config = Registry::getConfig();

    // get values
    if (isset($config->quota->exportAllowed)) {
      $exportAllowed = $config->quota->exportAllowed;
    } else {
      $exportAllowed = null;
    }

    return new ExportQuota($exportAllowed);
  }

  /**
   * Module related quotas
   * @return \Cms\Data\ModuleQuota
   */
  public function getModuleQuota()
  {
    $config = Registry::getConfig();

    // get values
    if (isset($config->quota->module->enableDev)) {
      $enableDev = $config->quota->module->enableDev;
    } else {
      $enableDev = null;
    }

    return new ModuleQuota($enableDev);
  }

  /**
   * Media related quotas
   * @return \Cms\Data\MediaQuota
   */
  public function getMediaQuota()
  {
    $config = Registry::getConfig();

    // get values
    if (isset($config->quota->media->maxFileSize)) {
      $maxFileSize = $config->quota->media->maxFileSize;
    } else {
      $maxFileSize = null;
    }
    if (isset($config->quota->media->maxSizePerWebsite)) {
      $maxWebsiteSize = $config->quota->media->maxSizePerWebsite;
    } else {
      $maxWebsiteSize = null;
    }

    return new MediaQuota($maxFileSize, $maxWebsiteSize);
  }

  /**
   * return TRUE if space is expired, FALSE otherwise.
   * @return bool
   */
  public function isSpaceExpired()
  {
    $config = Registry::getConfig();

    if (isset($config->quota) && isset($config->quota->expired)) {
      return ($config->quota->expired == true);
    } else {
      return true;
    }
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return array(
      'media' => $this->getMediaQuota()->toArray(),
      'website' => $this->getWebsiteQuota()->toArray(),
      'webhosting' => $this->getWebhostingQuota()->toArray(),
      'export' => $this->getExportQuota()->toArray(),
      'module' => $this->getModuleQuota()->toArray(),
      'expired' => $this->isSpaceExpired(),
    );
  }
}
