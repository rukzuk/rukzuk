<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Cms\Creator\Adapter\DynamicCreator\Exceptions\PreparePageException;
use Cms\Creator\CreatorConfig;
use Cms\Creator\CreatorContext;
use Cms\Creator\CreatorJobConfig;
use Seitenbau\Registry;

class PageCreator
{
  /**
   * @var \Cms\Creator\CreatorContext
   */
  private $creatorContext;
  /**
   * @var \Cms\Creator\CreatorConfig
   */
  private $creatorConfig;
  /**
   * @var \Cms\Creator\Adapter\DynamicCreator\CreatorStorage
   */
  private $creatorStorage;
  /**
   * @var SiteStructure
   */
  private $siteStructure;
  /**
   * @var CreatorJobConfig
   */
  private $jobConfig;

  /**
   * @param CreatorContext        $creatorContext
   * @param CreatorConfig         $creatorConfig ,
   * @param CreatorStorage $creatorStorage
   * @param SiteStructure         $siteStructure
   * @param CreatorJobConfig      $jobConfig
   */
  public function __construct(
      CreatorContext $creatorContext,
      CreatorConfig $creatorConfig,
      CreatorStorage $creatorStorage,
      SiteStructure $siteStructure,
      CreatorJobConfig $jobConfig
  ) {
    $this->creatorContext = $creatorContext;
    $this->creatorConfig = $creatorConfig;
    $this->creatorStorage = $creatorStorage;
    $this->siteStructure = $siteStructure;
    $this->jobConfig = $jobConfig;
  }

  /**
   * @param string $pageId
   */
  public function createPage($pageId)
  {
    $preparePageResult = $this->doPreparePageWithRetry($pageId);
    $this->addPageToStorage($preparePageResult);
    $storage = $this->getCreatorStorage();
    $storage->addModule($preparePageResult->getUsedModuleIds());
    $storage->addMedia($preparePageResult->getUsedMediaIds());
    $storage->addAlbum($preparePageResult->getUsedAlbumIds());
    $storage->addMediaUrlCalls($preparePageResult->getMediaUrlCalls());
  }

  /**
   * @param PreparePageResult $preparePageResult
   */
  protected function addPageToStorage(PreparePageResult $preparePageResult)
  {
    $storage = $this->getCreatorStorage();
    if ($preparePageResult->getLegacySupport()) {
      $storage->addLegacyPage(
          $preparePageResult->getPageId(),
          $preparePageResult->getPageMeta(),
          $preparePageResult->getPageGlobal(),
          $preparePageResult->getPageAttributes(),
          $preparePageResult->getPageContent(),
          $preparePageResult->getCssCacheValue()
      );
    } else {
      $storage->addPage(
          $preparePageResult->getPageId(),
          $preparePageResult->getPageMeta(),
          $preparePageResult->getPageGlobal(),
          $preparePageResult->getPageAttributes(),
          $preparePageResult->getPageContent(),
          $preparePageResult->getCssCacheValue()
      );
    }
  }

  /**
   * @return \Cms\Creator\CreatorContext
   */
  protected function getCreatorContext()
  {
    return $this->creatorContext;
  }

  /**
   * @return \Cms\Creator\CreatorConfig
   */
  protected function getCreatorConfig()
  {
    return $this->creatorConfig;
  }

  /**
   * @return \Cms\Creator\Adapter\DynamicCreator\CreatorStorage
   */
  protected function getCreatorStorage()
  {
    return $this->creatorStorage;
  }

  /**
   * @return \Cms\Creator\Adapter\DynamicCreator\SiteStructure
   */
  public function getSiteStructure()
  {
    return $this->siteStructure;
  }

  /**
   * @return \Cms\Creator\CreatorJobConfig
   */
  protected function getJobConfig()
  {
    return $this->jobConfig;
  }

  /**
   * @return string
   */
  protected function getWebsiteId()
  {
    return $this->getJobConfig()->getWebsiteId();
  }

  /**
   * @param $pageId
   *
   * @throws PreparePageException
   * @return PreparePageResult
   */
  protected function doPreparePageWithRetry($pageId)
  {
    $preparePageConfig = $this->getPreparePageConfig();
    $maxRetryLimit = (isset($preparePageConfig['maxRetryLimit']) ? $preparePageConfig['maxRetryLimit'] : 3);
    $retryCount = 0;
    do
    {
      try {
        return $this->doPreparePage($pageId);
      } catch (PreparePageException $doNothing) {}
    } while (++$retryCount <= $maxRetryLimit);

    throw new PreparePageException(__METHOD__ . ' max retries ('.$maxRetryLimit.') exceeded! Failed to prepare page ' . $pageId);
  }

  /**
   * @param $pageId
   *
   * @throws PreparePageException
   * @return PreparePageResult
   */
  protected function doPreparePage($pageId)
  {
    $websiteId = $this->getWebsiteId();

    Registry::getLogger()->log(
      __CLASS__,
      __METHOD__,
      sprintf('Call prepare page with page id "%s" and website id "%s"', $pageId, $websiteId),
      \Zend_Log::NOTICE
    );

    $params = array(
      'creatorname' => 'dynamic',
      'websiteid' => $websiteId,
      'prepare' => 'page',
      'info' => array(
        'id' => $pageId,
        'structure' => $this->getSiteStructure()->toArray(),
      )
    );

    $url = $this->getCreatorContext()->createTicketUrl(
        $websiteId,
        'creator',
        'prepare',
        $params
    );

    // http call
    $preparePageConfig = $this->getPreparePageConfig();
    $req = array(
      'url'           => $url,
      'timeout'       => (isset($preparePageConfig['timeout']) ? $preparePageConfig['timeout'] : null),
      'maxRedirects'  => (isset($preparePageConfig['maxRedirects']) ? $preparePageConfig['maxRedirects'] : null),
    );
    $responseBody = '';
    $responseHeader = array();
    $http = $this->getHttpClient();

    $timeStart = microtime(true);
    $responseCode = $http->callUrl('', $req, $responseHeader, $responseBody, $http::METHOD_GET);
    $timeEnd = microtime(true);

    $respObj = json_decode($responseBody, true);

    if (is_null($respObj)
      || (isset($respObj['success']) && $respObj['success'] == false)
      || (!isset($respObj['data'])  || (!is_array($respObj['data'])))
    ) {
      // Log failure
      Registry::getLogger()->log(
          __CLASS__,
          __METHOD__,
          sprintf(
              'Failed to prepare Page "%s" website "%s" error: "%s" responseCode: "%s" body: "%s"',
              $pageId,
              $websiteId,
              $http->getLastError(),
              $responseCode,
              $responseBody
          ),
          \Zend_Log::ERR
      );

      // throw simple exception
      throw new PreparePageException(__METHOD__ . ' failed to prepare page ' . $pageId);
    }

    Registry::getLogger()->log(
      __CLASS__,
      __METHOD__,
      sprintf('Call prepare page with page id "%s" and website id "%s" takes %d ms',
        $pageId, $websiteId, ($timeEnd - $timeStart) * 1000),
      \Zend_Log::NOTICE
    );

    return new PreparePageResult($respObj['data']);
  }

  /**
   * @return \Seitenbau\Http
   */
  protected function getHttpClient()
  {
    return new \Seitenbau\Http();
  }

  /**
   * @return array
   */
  protected function getPreparePageConfig()
  {
    $creatorConfig = $this->getCreatorConfig()->getConfig();
    if (isset($creatorConfig['pageCreator'])) {
      $preparePageConfig = $creatorConfig['pageCreator'];
    } else {
      $preparePageConfig = array();
    }

    return $preparePageConfig;
  }
}
