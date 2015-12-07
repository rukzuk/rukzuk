<?php


namespace Cms\Render\InfoStorage\WebsiteInfoStorage;

use Cms\Service\Website as WebsiteService;
use Cms\Service\WebsiteSettings as WebsiteSettingsService;
use Render\InfoStorage\WebsiteInfoStorage\Exceptions\WebsiteSettingsDoesNotExists;
use Render\InfoStorage\WebsiteInfoStorage\IWebsiteInfoStorage;

/**
 * Class ArrayBasedColorInfoStorage
 *
 * @package Cms\Render\InfoStorage\WebsiteInfoStorage
 */
class ServiceBasedWebsiteInfoStorage implements IWebsiteInfoStorage
{
  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var WebsiteService
   */
  private $websiteService;

  /**
   * @var WebsiteSettingsService
   */
  private $websiteSettingsService;


  /**
   * @param string                 $websiteId
   * @param WebsiteService         $websiteService
   * @param WebsiteSettingsService $websiteSettingsService
   */
  public function __construct(
      $websiteId,
      WebsiteService $websiteService,
      WebsiteSettingsService $websiteSettingsService
  ) {
    $this->websiteId = $websiteId;
    $this->websiteService = $websiteService;
    $this->websiteSettingsService = $websiteSettingsService;
  }

  /**
   * @param string $websiteSettingsId
   *
   * @return array
   * @throws WebsiteSettingsDoesNotExists
   */
  public function getWebsiteSettings($websiteSettingsId)
  {
    try {
      $websiteSettings = $this->getWebsiteSettingsService()->getById(
          $this->getWebsiteId(),
          $websiteSettingsId
      );
      return $this->objectToArray($websiteSettings->getFormValues());
    } catch (\Exception $e) {
      throw new WebsiteSettingsDoesNotExists();
    }
  }

  /**
   * ArrayBasedWebsiteInfoStorage constructor compatible array representation
   *
   * @return array
   */
  public function toArray()
  {
    $websiteSettingsAsArray = array();
    $allWebsiteSettings = $this->getWebsiteSettingsService()->getAll($this->getWebsiteId());
    foreach ($allWebsiteSettings as $id => $settings) {
      $websiteSettingsAsArray[$id] = $this->objectToArray($settings->getFormValues());
      if (!is_array($websiteSettingsAsArray[$id])) {
        $websiteSettingsAsArray[$id] = array();
      }
    }
    return $websiteSettingsAsArray;
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @return WebsiteSettingsService
   */
  protected function getWebsiteSettingsService()
  {
    return $this->websiteSettingsService;
  }

  /**
   * @param object $object
   *
   * @return array
   */
  protected function objectToArray($object)
  {
    return json_decode(json_encode($object), true);
  }
}
