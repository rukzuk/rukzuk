<?php


namespace Cms\Business;

use Cms\Creator\Adapter\AbstractCreator;
use Cms\Creator\CreatorJobConfig;
use Cms\Creator\Adapter\DynamicCreator\CreatorStorage;
use Seitenbau\Registry as Registry;
use Cms\Exception as CmsException;
use Cms\Response\Error as Error;
use Cms\Creator\CreatorContext;
use Cms\Creator\CreatorFactory;

/**
 * Provides the business logic for creating live versions of a website
 *
 * @package Cms\Business
 */
class Creator extends Base\Service
{
  /**
   * @param $websiteId
   *
   * @return \Cms\Data\Creator
   */
  public function createWebsite($websiteId)
  {
    try {
      $creator = $this->getCreator();
      $creatorJob = $this->createCreatorJob($websiteId);
      return $creator->createWebsite($creatorJob);
    } catch (Exception $e) {
      $this->getResponse()->setError(new Error($e));
    }
  }

  /**
   * @param string $creatorName
   * @param string $websiteId
   * @param string $prepare
   * @param array  $info
   *
   * @return array
   */
  public function prepare($creatorName, $websiteId, $prepare, array $info)
  {
    try {
      $creator = $this->getCreator($creatorName);
      $creatorJob = $this->createCreatorJob($websiteId);
      return $creator->prepare($creatorJob, $prepare, $info);
    } catch (Exception $e) {
      $this->getResponse()->setError(new Error($e));
    }
  }

  /**
   * @param null|string $creatorName
   *
   * @return AbstractCreator
   */
  protected function getCreator($creatorName = null)
  {
    $creatorContext = $this->createCreatorContext();
    $creatorFactory = new CreatorFactory();
    return $creatorFactory->createCreator($creatorContext, $creatorName);
  }

  /**
   * @return CreatorContext
   */
  protected function createCreatorContext()
  {
    $websiteBusiness = $this->getBusiness('Website');
    $websiteSettingsBusiness = $this->getBusiness('WebsiteSettings');
    $moduleBusiness = $this->getBusiness('Modul');
    $pageBusiness = $this->getBusiness('Page');
    $pageTypeBusiness = $this->getBusiness('PageType');
    $mediaBusiness = $this->getBusiness('Media');
    $ticketBusiness = $this->getBusiness('Ticket');
    return new CreatorContext(
        $websiteBusiness,
        $websiteSettingsBusiness,
        $moduleBusiness,
        $pageBusiness,
        $pageTypeBusiness,
        $mediaBusiness,
        $ticketBusiness
    );
  }

  /**
   * @param string $websiteId
   *
   * @return CreatorJobConfig
   */
  protected function createCreatorJob($websiteId)
  {
    $publisherBusiness = $this->getBusiness('Publisher');
    $publishConfig = $publisherBusiness->getWebsitePublish($websiteId);
    return new CreatorJobConfig($websiteId, $publishConfig);
  }

  /**
   * @param string $identity
   * @param string $rightName
   * @param array  $check
   *
   * @return boolean
   */
  protected function hasUserRights($identity, $rightName, $check)
  {
    if ($this->isSuperuser($identity)) {
      return true;
    }

    switch ($rightName) {
      case 'prepare':
            return $this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'creator', 'all');
        break;
    }

    return false;
  }
}
