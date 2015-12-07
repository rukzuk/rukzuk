<?php


namespace Cms\Dao\TemplateSnippet;

use Cms\Dao\TemplateSnippet as TemplateSnippetDaoInterface;
use Cms\Dao\Base\Filesystem as BaseFilesystemDao;
use Cms\Dao\Base\SourceItem;
use Cms\Data\TemplateSnippet as DataTemplateSnippet;
use Cms\Validator\TemplateSnippetId as TemplateSnippetIdValidator;
use Cms\Exception as CmsException;
use Seitenbau\Registry;
use Seitenbau\FileSystem as FS;
use Seitenbau\Log as SbLog;

/**
 * Filesystem dao for template snippets
 *
 * @package Cms\Dao\TemplateSnippet
 */
class Filesystem extends BaseFilesystemDao implements TemplateSnippetDaoInterface
{
  const PREVIEW_IMAGE_EXTENSION = 'svg';
  const PREVIEW_IMAGE_SUBDIRECTORY = 'assets';

  /**
   * @return string
   */
  protected function getManifestFileName()
  {
    return 'templateSnippet.json';
  }

  /**
   * returns all TemplateSnippets of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $orderDirection
   *
   * @return  DataTemplateSnippet[]
   */
  public function getAll(TemplateSnippetSource $snippetSource, $orderDirection = null)
  {
    $snippets = $this->internalGetAll($snippetSource);
    $this->sortSnippetsByName($snippets, $orderDirection);
    return $snippets;
  }

  /**
   * returns the specified "Template Snippets" of the given Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   *
   * @return  DataTemplateSnippet[]
   */
  public function getByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    $snippets = array();
    foreach ($ids as $id) {
      $snippets[] = $this->getById($snippetSource, $id);
    }
    return $snippets;
  }

  /**
   * return the TemplateSnippets of the given id and Website
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return  DataTemplateSnippet
   * @throws \Cms\Exception
   */
  public function getById(TemplateSnippetSource $snippetSource, $id)
  {
    return $this->internalGetById($snippetSource, $id);
  }

  /**
   * deletes the TemplateSnippets of the given ids and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param array                 $ids
   */
  public function deleteByIds(TemplateSnippetSource $snippetSource, array $ids)
  {
    foreach ($ids as $id) {
      $this->delete($snippetSource, $id);
    }
  }

  /**
   * deletes the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   * @throws ReadOnlyException
   */
  public function delete(TemplateSnippetSource $snippetSource, $id)
  {
    $this->internalGetById($snippetSource, $id);
    // read-only DAO
    throw new ReadOnlyException(1613, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * deletes all TemplateSnippets of the given website id
   *
   * @param TemplateSnippetSource $snippetSource
   *
   * @throws ReadOnlyException
   */
  public function deleteByWebsiteId(TemplateSnippetSource $snippetSource)
  {
    // read-only DAO
    throw new ReadOnlyException(1613, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * creates a new TemplateSnippet
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   * @throws ReadOnlyException
   */
  public function create(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    // read-only DAO
    throw new ReadOnlyException(1606, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * updates the TemplateSnippet of the given id and website id
   *
   * @param TemplateSnippetSource     $snippetSource
   * @param \Cms\Data\TemplateSnippet $snippet
   *
   * @return  DataTemplateSnippet
   * @throws ReadOnlyException
   */
  public function update(TemplateSnippetSource $snippetSource, DataTemplateSnippet $snippet)
  {
    $this->internalGetById($snippetSource, $snippet->getId());
    // read-only DAO
    throw new ReadOnlyException(1613, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * copy TemplateSnippets of the given ids and website id into another website
   *
   * @param TemplateSnippetSource $snippetSourceFrom
   * @param TemplateSnippetSource $snippetSourceTo
   * @param array                 $ids
   *
   * @return boolean
   * @throws ReadOnlyException
   */
  public function copyToNewWebsite(
      TemplateSnippetSource $snippetSourceFrom,
      TemplateSnippetSource $snippetSourceTo,
      array $ids = array()
  ) {
    // read-only DAO
    throw new ReadOnlyException(1613, __METHOD__, __LINE__, null, new \Exception('read only DAO'));
  }

  /**
   * Checks if there is a template snippet under the given TemplateSnippet Id and Website Id
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $id
   *
   * @return boolean
   */
  public function existsSnippet(TemplateSnippetSource $snippetSource, $id)
  {
    return $this->internalExists($snippetSource, $id);
  }

  /**
   * search over the TemplateSnippets content and returns the found TemplateSnippets
   *
   * @param TemplateSnippetSource $snippetSource
   * @param string                $needle
   *
   * @return  DataTemplateSnippet[]
   */
  public function searchInContent(TemplateSnippetSource $snippetSource, $needle)
  {
    $foundSnippets = array();
    $allSnippets = $this->getAll($snippetSource);
    foreach ($allSnippets as $snippet) {
      if (strpos($snippet->getContent(), $needle) !== false) {
        $foundSnippets[] = $snippet;
      }
    }
    return $foundSnippets;
  }

  /**
   * @return TemplateSnippetIdValidator
   */
  protected function createIdValidator()
  {
    return new TemplateSnippetIdValidator();
  }

  /**
   * @param array  $snippets
   * @param string $orderDirection
   */
  protected function sortSnippetsByName(array &$snippets, $orderDirection)
  {
    if (!is_string($orderDirection)) {
      return;
    }
    $orderDirection = strtoupper($orderDirection);
    if ($orderDirection == 'ASC') {
      usort($snippets, function ($a, $b) {
        return strnatcasecmp($a->getName(), $b->getName());
      });
    } elseif ($orderDirection == 'DESC') {
      usort($snippets, function ($a, $b) {
        return (strnatcasecmp($a->getName(), $b->getName()) * -1);
      });
    }
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param array      $additionalData
   *
   * @return \Cms\Data\TemplateSnippet
   */
  protected function loadEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData)
  {
    $manifest = $this->loadManifestFile($sourceItem);
    $lastUpdate = $this->getLastModifyTime($sourceItem);
    $previewImageUrl = $this->getPreviewImageUrl($sourceItem);
    return $this->loadDataObject(
        $websiteId,
        $id,
        $sourceItem,
        $manifest,
        $lastUpdate,
        $previewImageUrl
    );
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param \stdClass  $manifest
   * @param string     $lastUpdate
   * @param string     $previewImageUrl
   *
   * @return DataTemplateSnippet
   */
  protected function loadDataObject(
      $websiteId,
      $id,
      SourceItem $sourceItem,
      \stdClass $manifest,
      $lastUpdate,
      $previewImageUrl
  ) {
    $snippet = new DataTemplateSnippet();

    $snippet->setWebsiteid($websiteId);
    $snippet->setId($id);
    $snippet->setReadonly($sourceItem->isReadonly());
    $snippet->setSourceType($sourceItem->getType());
    $snippet->setLastupdate($lastUpdate);

    if (!empty($previewImageUrl)) {
      $snippet->setPreviewImageUrl($previewImageUrl);
    }

    if (property_exists($manifest, 'name')) {
      $snippet->setName($manifest->name);
    }
    if (property_exists($manifest, 'description')) {
      $snippet->setDescription($manifest->description);
    }
    if (property_exists($manifest, 'category')) {
      $snippet->setCategory($manifest->category);
    }
    if (property_exists($manifest, 'content')) {
      $snippet->setContent($manifest->content);
    }
    if (property_exists($manifest, 'baseLayout')) {
      $snippet->setBaseLayout($manifest->baseLayout);
    }
    if (property_exists($manifest, 'pageTypes') && is_array($manifest->pageTypes)) {
      $snippet->setPageTypes($manifest->pageTypes);
    }

    return $snippet;
  }

  /**
   * @param SourceItem $sourceItem
   *
   * @return null|string
   */
  protected function getPreviewImageUrl(SourceItem $sourceItem)
  {
    $imageFileName = 'templateSnippet.' . self::PREVIEW_IMAGE_EXTENSION;
    $imageFilePath = FS::joinPath(
        $sourceItem->getDirectory(),
        self::PREVIEW_IMAGE_SUBDIRECTORY,
        $imageFileName
    );
    if (!file_exists($imageFilePath)) {
      return null;
    }
    return $sourceItem->getUrl() . '/' . self::PREVIEW_IMAGE_SUBDIRECTORY . '/' . $imageFileName;
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwNotExistsException($method, $line, $data)
  {
    throw new CmsException(1602, $method, $line, $data);
  }

  /**
   * @param string $method
   * @param string $line
   * @param array  $data
   *
   * @throws CmsException
   */
  protected function throwGetByIdErrorException($method, $line, $data)
  {
    throw new CmsException(1603, $method, $line, $data);
  }

  /**
   * @return string
   */
  protected static function getCacheSectionName()
  {
    return __CLASS__;
  }
}
