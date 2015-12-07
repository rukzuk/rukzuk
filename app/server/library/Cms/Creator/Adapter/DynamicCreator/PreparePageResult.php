<?php
namespace Cms\Creator\Adapter\DynamicCreator;

/**
 * Class PreparePageResult
 *
 * NOTE: If you add ANYTHING to this class,
 *       make sure toArray and fromArray supports it,
 *       otherwise it will NOT work as you would expect!
 *
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class PreparePageResult
{

  /**
   * @var string
   */
  private $websiteId;

  /**
   * @var string
   */
  private $pageId;
  /**
   * @var array
   */
  private $pageMeta = array();
  /**
   * @var array
   */
  private $pageGlobal = array();
  /**
   * @var array
   */
  private $pageAttributes = array();
  /**
   * @var array
   */
  private $pageContent = array();
  /**
   * @var bool
   */
  private $legacySupport = false;
  /**
   * @var array
   */
  private $usedModuleIds = array();
  /**
   * @var array
   */
  private $usedMediaIds = array();
  /**
   * @var array
   */
  private $usedAlbumIds = array();

  /**
   * @var string
   */
  private $cssCacheValue = '';

  /**
   * @var array
   */
  private $mediaUrlCalls = array();

  /**
   * @var string
   */
  private $htmlCacheValue = '';

  /**
   * @param array $mediaUrlCalls
   */
  public function setMediaUrlCalls($mediaUrlCalls)
  {
    $this->mediaUrlCalls = $mediaUrlCalls;
  }

  /**
   * @return array
   */
  public function getMediaUrlCalls()
  {
    return $this->mediaUrlCalls;
  }

  /**
   * @param array $array
   */
  public function __construct(array $array = array())
  {
    $this->fromArray($array);
  }

  /**
   * @param string $cssCacheValue
   */
  public function setCssCacheValue($cssCacheValue)
  {
    $this->cssCacheValue = $cssCacheValue;
  }

  /**
   * @return string
   */
  public function getCssCacheValue()
  {
    return $this->cssCacheValue;
  }

  /**
   * @param boolean $legacySupport
   */
  public function setLegacySupport($legacySupport)
  {
    $this->legacySupport = $legacySupport;
  }

  /**
   * @return boolean
   */
  public function getLegacySupport()
  {
    return $this->legacySupport;
  }

  /**
   * @param array $pageContent
   */
  public function setPageContent($pageContent)
  {
    $this->pageContent = $pageContent;
  }

  /**
   * @return array
   */
  public function getPageContent()
  {
    return $this->pageContent;
  }

  /**
   * @param array $pageGlobal
   */
  public function setPageGlobal($pageGlobal)
  {
    $this->pageGlobal = $pageGlobal;
  }

  /**
   * @return array
   */
  public function getPageGlobal()
  {
    return $this->pageGlobal;
  }

  /**
   * @param array $pageAttributes
   */
  public function setPageAttributes($pageAttributes)
  {
    $this->pageAttributes = $pageAttributes;
  }

  /**
   * @return array
   */
  public function getPageAttributes()
  {
    return $this->pageAttributes;
  }

  /**
   * @param string $pageId
   */
  public function setPageId($pageId)
  {
    $this->pageId = $pageId;
  }

  /**
   * @return string
   */
  public function getPageId()
  {
    return $this->pageId;
  }

  /**
   * @param array $pageMeta
   */
  public function setPageMeta($pageMeta)
  {
    $this->pageMeta = $pageMeta;
  }

  /**
   * @return array
   */
  public function getPageMeta()
  {
    return $this->pageMeta;
  }

  /**
   * @param array $usedAlbumIds
   */
  public function setUsedAlbumIds($usedAlbumIds)
  {
    $this->usedAlbumIds = $usedAlbumIds;
  }

  /**
   * @param array $usedAlbumIds
   */
  public function addUsedAlbumIds($usedAlbumIds)
  {
    if (count($usedAlbumIds) <= 0) {
      return;
    }
    $this->usedAlbumIds = array_unique(array_merge($usedAlbumIds, $this->usedAlbumIds));

  }

  /**
   * @return array
   */
  public function getUsedAlbumIds()
  {
    return $this->usedAlbumIds;
  }

  /**
   * @param array $usedMediaIds
   */
  public function setUsedMediaIds($usedMediaIds)
  {
    $this->usedMediaIds = $usedMediaIds;
  }

  /**
   * @param array $usedMediaIds
   */
  public function addUsedMediaIds(array $usedMediaIds)
  {
    if (count($usedMediaIds) <= 0) {
      return;
    }
    $this->usedMediaIds = array_unique(array_merge($usedMediaIds, $this->usedMediaIds));
  }

  /**
   * @return array
   */
  public function getUsedMediaIds()
  {
    return $this->usedMediaIds;
  }

  /**
   * @param array $usedModuleIds
   */
  public function setUsedModuleIds($usedModuleIds)
  {
    $this->usedModuleIds = $usedModuleIds;
  }

  /**
   * @return array
   */
  public function getUsedModuleIds()
  {
    return $this->usedModuleIds;
  }

  /**
   * @param string $websiteId
   */
  public function setWebsiteId($websiteId)
  {
    $this->websiteId = $websiteId;
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }

  /**
   * @param string $htmlCacheValue
   */
  public function setHtmlCacheValue($htmlCacheValue)
  {
    $this->htmlCacheValue = $htmlCacheValue;
  }

  /**
   * @return string
   */
  public function getHtmlCacheValue()
  {
    return $this->htmlCacheValue;
  }

  /**
   * 'Serialize' the whole object to an flat php array
   * @return array
   */
  public function toArray()
  {
    return array(
      'websiteId' => $this->getWebsiteId(),
      'id' => $this->getPageId(),
      'meta' => $this->getPageMeta(),
      'global' => $this->getPageGlobal(),
      'pageAttributes' => $this->getPageAttributes(),
      'content' => $this->getPageContent(),
      'legacy' => $this->getLegacySupport(),
      'moduleIds' => $this->getUsedModuleIds(),
      'mediaIds' => $this->getUsedMediaIds(),
      'albumIds' => $this->getUsedAlbumIds(),
      'cssCache' => $this->getCssCacheValue(),
      'mediaUrlCalls' => $this->getMediaUrlCalls(),
      'htmlCache' => $this->getHtmlCacheValue(),
    );
  }

  /**
   * Init the properties with data form the array (must be compatible with {@link #toArray})
   *
   * @param $array
   */
  private function fromArray($array)
  {
    if (isset($array['websiteId'])) {
      $this->setWebsiteId($array['websiteId']);
    }

    if (isset($array['id'])) {
      $this->setPageId($array['id']);
    }

    if (isset($array['meta'])) {
      $this->setPageMeta($array['meta']);
    }

    if (isset($array['global'])) {
      $this->setPageGlobal($array['global']);
    }

    if (isset($array['pageAttributes'])) {
      $this->setPageAttributes($array['pageAttributes']);
    }

    if (isset($array['content'])) {
      $this->setPageContent($array['content']);
    }

    if (isset($array['legacy'])) {
      $this->setLegacySupport($array['legacy']);
    }

    if (isset($array['moduleIds'])) {
      $this->setUsedModuleIds($array['moduleIds']);
    }

    if (isset($array['mediaIds'])) {
      $this->setUsedMediaIds($array['mediaIds']);
    }

    if (isset($array['albumIds'])) {
      $this->setUsedAlbumIds($array['albumIds']);
    }

    if (isset($array['cssCache'])) {
      $this->setCssCacheValue($array['cssCache']);
    }

    if (isset($array['mediaUrlCalls'])) {
      $this->setMediaUrlCalls($array['mediaUrlCalls']);
    }

    if (isset($array['htmlCache'])) {
      $this->setHtmlCacheValue($array['htmlCache']);
    }

  }
}
