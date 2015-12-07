<?php
namespace Cms\Service;

use Cms\Service\Base\Plain as PlainServiceBase;
use Cms\Exception as CmsException;
use Cms\Publisher\Factory as PublisherFactory;
use Cms\Business\Website as WebsiteBusiness;
use Cms\Business\Builder as BuilderBusiness;
use Cms\Data\Build as BuildData;
use Cms\Data\PublisherStatus as PublisherStatusData;
use Cms\Validator\PublishedId as PublishedIdValidator;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as SbLog;
use Seitenbau\FileSystem as FS;
use Seitenbau\Json as SbJson;
use Seitenbau\UniqueIdGenerator as UniqueIdGenerator;

/**
 * Publisher Service
 *
 * @package      Cms
 * @subpackage   Service
 */
class Publisher extends PlainServiceBase
{
  const PUBLISHED_INFOFILE_SUFFIX = '.published.json';


  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @throws \Cms\Exception
   * @throws \Exception
   * @return \Cms\Data\PublisherStatus
   */
  public function publishWebsite($websiteId, $buildId)
  {
    $websiteToPublish = $this->getWebsiteById($websiteId);
    if (!$websiteToPublish->getPublishingEnabled()) {
      throw new CmsException('624', __METHOD__, __LINE__, array(
        'websiteId' => $websiteId,
      ));
    }

    $publishConfig = $this->getWebsitePublishFromWebsiteData($websiteToPublish);
    if (is_array($publishConfig) && count($publishConfig) === 0) {
      throw new CmsException('623', __METHOD__, __LINE__);
    }
    
    $this->publishingAllowed($websiteId, $buildId, $publishConfig);
      
    $publishingId = $this->createPublishingId($websiteId, $buildId);
    $initPublishedStatus = $this->setPublisherStatusToInit($websiteId, $buildId, $publishingId);
    $publishingFilePath = $this->preparePublishing($websiteId, $buildId, $publishingId, $publishConfig);
    
    try {
      $publisher = $this->getPublisher();
      $publishedStatus = $publisher->publish(
          $websiteId,
          $publishingId,
          $publishingFilePath,
          $publishConfig,
          array(
          'download' => $this->getDownloadUrlForPublishingId($websiteId, $publishingId),
          'status'   => $this->getStatusChagedUrlForPublishingId($websiteId, $buildId, $publishingId),
          )
      );
    } catch (\Exception $e) {
      $this->updatePublisherStatusToFailed($websiteId, $buildId, $initPublishedStatus, $e->getMessage());
      $this->removingPublishingFiles($websiteId, $publishingId);
      throw $e;
    }

    $publishedStatus->setTimestamp($initPublishedStatus->getTimestamp());
    try {
      $this->setPublisherStatus($websiteId, $buildId, $publishedStatus);
    } catch (\Exception $doNothing) {
    }

    return $publishedStatus;
  }

  /**
   * Removes Published Website
   * @param $websiteId
   * @throws \Cms\Exception
   */
  public function deletePublishedWebsite($websiteId)
  {
    $website = $this->getWebsiteById($websiteId);
    $publishConfig = $this->getWebsitePublishFromWebsiteData($website);
    if (is_array($publishConfig) && count($publishConfig) === 0) {
      return;
    }

    try {
      $publisher = $this->getPublisher();
      $publisher->delete($websiteId, $publishConfig);
    } catch (\Exception $e) {
      Registry::getLogger()->logException(__METHOD__, __LINE__, $e, SbLog::WARN);
    }
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @return \Cms\Data\PublisherStatus
   * @throws \Cms\Exception
   */
  public function getPublishedStatusByBuildId($websiteId, $buildId)
  {
    $publishedStatus = $this->readPublisherStatusFromCache($websiteId, $buildId);
    $this->updatePublisherStatus($websiteId, $buildId, $publishedStatus);
    return $publishedStatus;
  }

  /**
   * @param string|null $type
   *
   * @return array
   */
  public function getDefaultPublishData($type = null)
  {
    $publisher = $this->getPublisher();
    return $publisher->getDefaultPublishData($type = null);
  }

  /**
   * Get full publish configuration
   * @param  string $websiteId
   * @return array
   */
  public function getWebsitePublish($websiteId)
  {
    $website = $this->getWebsiteById($websiteId);
    return $this->getWebsitePublishFromWebsiteData($website);
  }

  /**
   * Get full publish configuration
   * @param  \Cms\Data\Website $website
   * @return array
   */
  public function getWebsitePublishFromWebsiteData($website)
  {
    $publisher = $this->getPublisher();
    return $publisher->getPublishData($website);
  }

  /**
   * Internal Live Domain (e.g. ef3sbae.zuk.io)
   * @param $websiteId
   * @return string
   */
  public function getInternalLiveUrl($websiteId)
  {
    $website = $this->getWebsiteById($websiteId);
    $publisher = $this->getPublisher();
    return $publisher->getInternalLiveUrl($website);
  }

  /**
   * Returns the available publishing types
   * @return array
   */
  public function getSupportedPublishTypes()
  {
    $publisher = $this->getPublisher();
    return $publisher->getSupportedPublishTypes();
  }

  /**
   * Returns the live url (based on the publish mode and the provided data)
   * http://an.example.com/your/site
   * @param $websiteId
   * @return string
   */
  public function getLiveUrl($websiteId)
  {
    $website = $this->getWebsiteById($websiteId);
    $publishData = $this->getWebsitePublish($websiteId);
    $publisher = $this->getPublisher();
    return $publisher->getLiveUrl($website, $publishData);
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @throws \Cms\Exception
   * @return \Cms\Data\PublisherStatus
   */
  private function getPublishedStatusFromPublisher($websiteId, $publishingId)
  {
    $website = $this->getWebsiteById($websiteId);
    $publishConfig = $this->getWebsitePublishFromWebsiteData($website);
    if (is_array($publishConfig) && count($publishConfig) === 0) {
      throw new CmsException('623', __METHOD__, __LINE__);
    }
    
    $publisher = $this->getPublisher();
    $publishedStatus = $publisher->getStatus(
        $websiteId,
        $publishingId,
        $publishConfig,
        array(
        'download' => $this->getDownloadUrlForPublishingId($websiteId, $publishingId),
        )
    );
    $publishedStatus->setId($publishingId);

    return $publishedStatus;
  }
  
  private function updatePublisherStatus($websiteId, $buildId, PublisherStatusData &$publishedStatus)
  {
    // if old status is init, update status only if status 60 sec old
    if ($publishedStatus->getStatus() == PublisherStatusData::STATUS_INIT
        && $publishedStatus->getTimestamp() >= (time()-60)
    ) {
      return;
    }
    
    if (!$publishedStatus->isPublishing()) {
      $this->removingPublishingFilesOnSpecialStates($websiteId, $publishedStatus);
      return;
    }

    try {
      if (is_null($publishedStatus->getId())) {
        $this->updatePublisherStatusToFailed($websiteId, $buildId, $publishedStatus);
        Registry::getLogger()->log(__METHOD__, __LINE__, "no publisher id given", SbLog::ERR);
        return;
      }
    
      $newPublishedData = $this->getPublishedStatusFromPublisher($websiteId, $publishedStatus->getId());
      if ($newPublishedData->getStatus() == PublisherStatusData::STATUS_UNKNOWN) {
        $this->updatePublisherStatusToFailed($websiteId, $buildId, $publishedStatus);
        Registry::getLogger()->log(
            __METHOD__,
            __LINE__,
            sprintf("unknown publisher job with id %s", $publishedStatus->getId()),
            SbLog::ERR
        );
        return;
      }
      $newPublishedData->setTimestamp($publishedStatus->getTimestamp());
      $publishedStatus = $newPublishedData;
      $this->setPublisherStatus($websiteId, $buildId, $publishedStatus);
      return;
      
    } catch (\Exception $logOnly) {
      // don't set the status to failed, because no connection to publisher
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
      return;
    }
  }
  
  private function updatePublisherStatusToFailed($websiteId, $buildId, PublisherStatusData &$publishedStatus, $msg = null)
  {
    try {
      if (empty($msg) && !is_string($msg)) {
        $translate = Registry::get('Zend_Translate');
        $msg = $translate->_('publisher.published_status.error.status_unknown');
      }
      $publishedStatus->setStatus(PublisherStatusData::STATUS_FAILED);
      $publishedStatus->setMsg($msg);
      $this->setPublisherStatus($websiteId, $buildId, $publishedStatus);
    } catch (\Exception $logOnly) {
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
      return;
    }
  }

  
  private function setPublisherStatusToInit($websiteId, $buildId, $publishingId)
  {
    $publishedStatus = new PublisherStatusData();
    $publishedStatus->setId($publishingId);
    $publishedStatus->setStatus(PublisherStatusData::STATUS_INIT);
    $publishedStatus->setTimestamp(time());
    $this->setPublisherStatus($websiteId, $buildId, $publishedStatus);
    return $publishedStatus;
  }
  
  private function setPublisherStatus($websiteId, $buildId, PublisherStatusData &$publishedStatus)
  {
    $this->writePublisherStatusToCache($websiteId, $buildId, $publishedStatus);
    $this->removingPublishingFilesOnSpecialStates($websiteId, $publishedStatus);
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @throws \Cms\Exception
   * @return array
   */
  private function createPublishingId($websiteId, $buildId)
  {
    $publishedId = $buildId.'.'.time().'.'.UniqueIdGenerator::v4();
    $validator = new PublishedIdValidator();
    if (!$validator->isValid($publishedId)) {
      throw new CmsException('2', __METHOD__, __LINE__, array('message' => 'wrong published id format'));
    }
    return $publishedId;
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @param  string $publishingId
   * @param  array $publishConfig
   * @throws \Exception
   * @return array
   */
  private function preparePublishing($websiteId, $buildId, $publishingId, array $publishConfig)
  {
    $publishingFilePath = null;
    try {
      $builderBusiness = $this->getBuilderBusiness();
      $buildFilePath = $builderBusiness->getWebsiteBuildFilePath($websiteId, $buildId);
      $publishingFilePath = $this->copyBuildToPublishingDirectory($websiteId, $publishingId, $buildFilePath);
    } catch (\Exception $e) {
      if (!empty($publishingFilePath)) {
        try {
          FS::rmFile($publishingFilePath);
        } catch (\Exception $logOnly) {
          Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
        }
      }
      throw $e;
    }
    
    return $publishingFilePath;
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @param  array $publishConfig
   * @throws \Cms\Exception
   * @return array
   */
  private function publishingAllowed($websiteId, $buildId, array $publishConfig)
  {
    $builderBusiness = $this->getBuilderBusiness();
    $buildData = $builderBusiness->getWebsiteBuildById($websiteId, $buildId, false);
    if (!($buildData instanceof BuildData) || $buildData->getBuilderVersion() != BuilderBusiness::VERSION) {
      throw new CmsException('904', __METHOD__, __LINE__);
    }
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @param  string $buildFilePath
   * @return string path to publishing file
   */
  private function copyBuildToPublishingDirectory($websiteId, $publishingId, $buildFilePath)
  {
    $config = Registry::getConfig();
    $publishingDirectory  = FS::joinPath($config->publisher->data->directory, $websiteId);
    FS::createDirIfNotExists($publishingDirectory, true);
    $publishingFilePath = FS::joinPath($publishingDirectory, $publishingId.'.zip');
    FS::copyFile($buildFilePath, $publishingFilePath);
    return $publishingFilePath;
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
   * @return array
   */
  private function getDownloadUrlForPublishingId($websiteId, $publishingId)
  {
    $config = Registry::getConfig();
    return sprintf(
        "%s%s/%s/%s.zip",
        Registry::getBaseUrl(),
        $config->publisher->data->webpath,
        $websiteId,
        $publishingId
    );
  }

  /**
   * @param  string $websiteId
   * @param  string $buildId
   * @param  string $publishingId
   * @return array
   */
  private function getStatusChagedUrlForPublishingId($websiteId, $buildId, $publishingId)
  {
    $config = Registry::getConfig();
    $paramsAsjson = array(
      'websiteId'   => $websiteId,
      'buildId'     => $buildId,
      'publishedId' => $publishingId,
    );
    return sprintf(
        "%s%s/builder/publisherstatuschanged/params/%s",
        Registry::getBaseUrl(),
        $config->server->url,
        urlencode(SbJson::encode($paramsAsjson))
    );
  }
  
  private function getPublisherStatusCacheFilePath($websiteId, $buildId)
  {
    $builderBusiness = $this->getBuilderBusiness();
    $buildFilePath = $builderBusiness->getWebsiteBuildFilePath($websiteId, $buildId);
    return FS::joinPath(dirname($buildFilePath), $buildId.self::PUBLISHED_INFOFILE_SUFFIX);
  }
    
  private function readPublisherStatusFromCache($websiteId, $buildId)
  {
    $publishedStatus = new PublisherStatusData();
    $publishedStatus->setStatus(PublisherStatusData::STATUS_UNKNOWN);
    
    $publishedInfoFilePath = $this->getPublisherStatusCacheFilePath($websiteId, $buildId);
    if (file_exists($publishedInfoFilePath)) {
      $publishedStatus->setFromArray(SbJson::decode(FS::readContentFromFile($publishedInfoFilePath), SbJson::TYPE_ARRAY));
    }
    return $publishedStatus;
  }
  
  private function writePublisherStatusToCache($websiteId, $buildId, PublisherStatusData $publishedStatus)
  {
    $publishedInfoFilePath = $this->getPublisherStatusCacheFilePath($websiteId, $buildId);
    FS::writeContentToFile($publishedInfoFilePath, SbJson::encode($publishedStatus->toArray()));
  }

  /**
   * @param  string $websiteId
   * @param  \Cms\Data\PublisherStatus $publishedStatus
  */
  private function removingPublishingFilesOnSpecialStates($websiteId, $publishedStatus)
  {
    $deletePublishingFileOnStatus = array(
      PublisherStatusData::STATUS_FINISHED,
      PublisherStatusData::STATUS_FAILED,
    );
    if (in_array($publishedStatus->getStatus(), $deletePublishingFileOnStatus)) {
      $this->removingPublishingFiles($websiteId, $publishedStatus->getId());
    }
  }

  /**
   * @param  string $websiteId
   * @param  string $publishingId
  */
  private function removingPublishingFiles($websiteId, $publishingId)
  {
    $config = Registry::getConfig();
    try {
      FS::rmFile(FS::joinPath($config->publisher->data->directory, $websiteId, $publishingId.'.zip'));
    } catch (\Exception $logOnly) {
      Registry::getLogger()->log(__METHOD__, __LINE__, $logOnly->getMessage(), SbLog::ERR);
    }
  }

  /**
   * @return \Cms\Publisher\Publisher
   */
  private function getPublisher()
  {
    return PublisherFactory::get();
  }

  /**
   * @return \Cms\Business\Builder
   */
  private function getBuilderBusiness()
  {
    return new BuilderBusiness('Builder');
  }

  /**
   * @param $websiteId
   *
   * @return \Cms\Data\Website
   * @throws \Cms\Exception
   */
  private function getWebsiteById($websiteId)
  {
    $websiteBusiness = new WebsiteBusiness('Website');
    return $websiteBusiness->getById($websiteId);
  }
}
