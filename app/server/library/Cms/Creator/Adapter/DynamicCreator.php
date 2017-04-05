<?php


namespace Cms\Creator\Adapter;

use Cms\Creator\CreatorJobConfig;
use Cms\Creator\Adapter\DynamicCreator\PageCreator;
use Cms\Creator\Adapter\DynamicCreator\PreparePage;
use Cms\Creator\Adapter\DynamicCreator\SiteStructure;
use Cms\Creator\Adapter\DynamicCreator\CreatorStorage;
use Cms\Creator\Adapter\DynamicCreator\MediaHelper;
use Seitenbau\FileSystem as FS;

class DynamicCreator extends AbstractCreator
{
  const CRATOR_NAME     = 'Dynamic';
  const CRATOR_VERSION  = '2.0';

  /**
   * @var array
   */
  private $reservedDirectories = array(
    'files'
  );

  /**
   * @var MediaHelper
   */
  private $mediaHelper;

  /**
   * initialize creator
   */
  protected function init()
  {
  }

  /**
   * @param CreatorJobConfig $jobConfig
   *
   * @return \Cms\Data\Creator
   */
  public function createWebsite(CreatorJobConfig $jobConfig)
  {
    $websiteId = $jobConfig->getWebsiteId();
    $structure = $this->createInitializedSiteStructure($websiteId);
    $storage = $this->createStorage($websiteId, $structure, null);
    $pageCreator = $this->createPageCreator($storage, $jobConfig, $structure);
    $pageIds = $this->getPageIds($websiteId);
    foreach ($pageIds as $pageId) {
      $pageCreator->createPage($pageId);
    }
    // website global data (not per page nor usage based) TODO: this could be done in creator storage
    $creatorContext = $this->getCreatorContext();
    $websiteSettings = $creatorContext->getWebsiteInfoStorage($websiteId)->toArray();
    $this->addUsedMediaAndAlbumIdsFromData($storage, $websiteSettings);
    $storage->setWebsiteSettings($websiteSettings);
    $storage->setColors($creatorContext->getColorInfoStorage($websiteId)->toArray());
    $storage->setResolutions($creatorContext->getResolutions($websiteId));
    $storage->setNavigation($creatorContext->getNavigation($websiteId));
    $storage->finalize();
    return $storage->getCreatorData();
  }

  /**
   * @param CreatorJobConfig $jobConfig
   * @param string           $prepare
   * @param array            $info
   *
   * @return mixed
   */
  public function prepare(CreatorJobConfig $jobConfig, $prepare, array $info)
  {
    switch (strtolower($prepare)) {
      case 'page':
        $preparePage = $this->createPreparePage($jobConfig, $info);
        $result = $preparePage->prepare();
            return $result->toArray();
        break;
    }
  }

  /**
   * @param string $websiteId
   * @param SiteStructure $structure
   * @param string $workingDirectoryName
   *
   * @return CreatorStorage
   */
  public function createStorage($websiteId, SiteStructure $structure, $workingDirectoryName)
  {
    return new CreatorStorage(
        $this->getCreatorContext(),
        $structure,
        $this->getCreatorConfig()->getWorkingDirectory(),
        $websiteId,
        self::CRATOR_NAME,
        self::CRATOR_VERSION,
        $workingDirectoryName
    );
  }

  /**
   * @param string $websiteId
   *
   * @return SiteStructure
   */
  protected function createInitializedSiteStructure($websiteId)
  {
    $structure = new SiteStructure($this->getCreatorContext());
    $structure->initByWebsiteId($websiteId, $this->reservedDirectories);
    return $structure;
  }

  /**
   * @param $websiteId
   *
   * @return string[]
   */
  protected function getPageIds($websiteId)
  {
    return $this->getCreatorContext()->getPageIds($websiteId);
  }

  /**
   * @param CreatorStorage   $creatorStorage
   * @param CreatorJobConfig $jobConfig
   * @param SiteStructure    $siteStructure
   *
   * @internal param string $websiteId
   * @return PageCreator
   */
  protected function createPageCreator(
      CreatorStorage $creatorStorage,
      CreatorJobConfig $jobConfig,
      SiteStructure $siteStructure
  ) {
    return new PageCreator(
        $this->getCreatorContext(),
        $this->getCreatorConfig(),
        $creatorStorage,
        $siteStructure,
        $jobConfig
    );
  }

  /**
   * @param CreatorJobConfig $jobConfig
   * @param array            $info
   *
   * @return PreparePage
   */
  protected function createPreparePage(
      CreatorJobConfig $jobConfig,
      array $info
  ) {
    $that = $this;
    return new PreparePage(
      $this->getCreatorContext(),
      $jobConfig->getWebsiteId(),
      $info,
      function ($websiteId, SiteStructure $structure, $workingDirectoryName) use (&$that) {
        return $that->createStorage($websiteId, $structure, $workingDirectoryName);
      }
    );
  }

  /**
   * @param CreatorStorage $creatorStorage
   * @param mixed          $data
   */
  protected function addUsedMediaAndAlbumIdsFromData(CreatorStorage $creatorStorage, $data)
  {
    $mediaHelperResult = $this->getMediaHelper()->findMediaAndAlbumIds($data);
    $creatorStorage->addMedia($mediaHelperResult->getMediaIds());
    $creatorStorage->addAlbum($mediaHelperResult->getAlbumsIds());
  }

  /**
   * @return MediaHelper
   */
  protected function getMediaHelper()
  {
    if (!isset($this->mediaHelper)) {
      $this->mediaHelper = new MediaHelper();
    }
    return $this->mediaHelper;
  }
}
