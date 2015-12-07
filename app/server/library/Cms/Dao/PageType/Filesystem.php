<?php


namespace Cms\Dao\PageType;

use Cms\Dao\Base\Filesystem as BaseFilesystemDao;
use Cms\Dao\Base\SourceItem;
use Cms\Dao\PageType as PageTypeDaoInterface;
use Cms\Dao\PageType\Source as PageTypeSource;
use Cms\Data\PageType as DataPageType;
use Cms\Exception as CmsException;
use Cms\Validator\PageTypeId as PageTypeIdValidator;
use Seitenbau\FileSystem as FS;

/**
 * Filesystem dao for page types
 *
 * @package Cms\Dao\PageType
 */
class Filesystem extends BaseFilesystemDao implements PageTypeDaoInterface
{
  const PREVIEW_IMAGE_EXTENSION = 'svg';
  const PREVIEW_IMAGE_SUBDIRECTORY = 'assets';

  /**
   * @return string
   */
  protected function getManifestFileName()
  {
    return 'pageType.json';
  }

  /**
   * returns all page types of the given source
   *
   * @param PageTypeSource $source
   *
   * @return  DataPageType[]
   */
  public function getAll(PageTypeSource $source)
  {
    return $this->internalGetAll($source);
  }

  /**
   * returns the page type of the given source and id
   *
   * @param PageTypeSource $source
   * @param string         $id
   *
   * @return DataPageType
   */
  public function getById(PageTypeSource $source, $id)
  {
    return $this->internalGetById($source, $id);
  }

  /**
   * @return PageTypeIdValidator
   */
  protected function createIdValidator()
  {
    return new PageTypeIdValidator();
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param array      $additionalData
   *
   * @return object
   * @throws \Exception
   */
  protected function loadEntity($websiteId, $id, SourceItem $sourceItem, array $additionalData)
  {
    $manifest = $this->loadManifestFile($sourceItem);
    $previewImageUrl = $this->getPreviewImageUrl($sourceItem);
    return $this->loadDataObject($websiteId, $id, $sourceItem, $manifest, $previewImageUrl);
  }

  /**
   * @param string     $websiteId
   * @param string     $id
   * @param SourceItem $sourceItem
   * @param \stdClass  $manifest
   * @param string     $previewImageUrl
   *
   * @return DataPageType
   */
  protected function loadDataObject(
      $websiteId,
      $id,
      SourceItem $sourceItem,
      \stdClass $manifest,
      $previewImageUrl
  ) {
    $pageType = new DataPageType();

    $pageType->setWebsiteid($websiteId);
    $pageType->setId($id);
    $pageType->setReadonly($sourceItem->isReadonly());
    $pageType->setSourceType($sourceItem->getType());
    $pageType->setSource($sourceItem);

    if (property_exists($manifest, 'name') && is_object($manifest->name)) {
      $pageType->setName($manifest->name);
    }
    if (property_exists($manifest, 'description') && is_object($manifest->description)) {
      $pageType->setDescription($manifest->description);
    }
    if (property_exists($manifest, 'version')) {
      $pageType->setVersion($manifest->version);
    }
    if (property_exists($manifest, 'form')) {
      $pageType->setForm($manifest->form);
    }
    if (property_exists($manifest, 'formValues')) {
      $pageType->setFormValues($manifest->formValues);
    }

    if (!empty($previewImageUrl)) {
      $pageType->setPreviewImageUrl($previewImageUrl);
    }

    return $pageType;
  }

  /**
   * @param SourceItem $sourceItem
   *
   * @return null|string
   */
  protected function getPreviewImageUrl(SourceItem $sourceItem)
  {
    $imageFileName = 'pageType.' . self::PREVIEW_IMAGE_EXTENSION;
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
    throw new CmsException(2702, $method, $line, $data);
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
    throw new CmsException(2703, $method, $line, $data);
  }

  /**
   * @return string
   */
  protected static function getCacheSectionName()
  {
    return __CLASS__;
  }
}
