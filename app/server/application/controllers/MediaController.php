<?php
use Cms\Controller as Controller;
use Cms\Response\Media as Response;
use Cms\Service\Media\File as MediaFileService;
use Cms\Exception as CmsException;
use Dual\Media\Type as MediaType;
use Seitenbau\Mimetype as Mimetype;
use Seitenbau\Registry as Registry;
use Seitenbau\Log as Log;
use Seitenbau\FileSystem as FS;
use Seitenbau\File\TransferFactory as TransferFactory;
use Cms\Render\InfoStorage\MediaInfoStorage\ServiceBasedMediaInfoStorage;
use Cms\Render\MediaUrlHelper\CmsCDNMediaUrlHelper;
use Render\IconHelper\SimpleIconHelper;
use Render\ImageToolFactory\SimpleImageToolFactory;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use \Cms\Request\Base as CmsRequestBase;
use Render\MediaUrlHelper\ValidationHelper\SecureFileValidationHelper;

/**
 * MediaController
 *
 * @package      Application
 * @subpackage   Controller
 */

class MediaController extends Controller\Action
{
  public function init()
  {
    $this->initBusiness('Media');
    parent::init();
  }

  public function uploadAction()
  {
    $this->setContentTypeValue('text/plain');
    $validatedRequest = $this->getValidatedRequest('Media', 'Upload');

    $this->getBusiness()->checkUserRights('upload', array(
      'websiteId' => $validatedRequest->getWebsiteId(),
      'id'        => $validatedRequest->getId(),
      'albumId'   => $validatedRequest->getAlbumId(),
    ));

    $upload = TransferFactory::getAdapter();
    $upload->setOptions(array('useByteString' => false));

    if (!$upload->isValid($validatedRequest->getFileInputname())) {
      throw new CmsException(220, __METHOD__, __LINE__);
    }
    
    $this->checkQuota($validatedRequest);
    
    $media = $this->createOrUpdateItemFromUploadRequest($upload, $validatedRequest);
    $this->setUploadedFileToMediaItem($upload, $media, $validatedRequest->getUploadFilename());

    $this->responseData->setData(array('id' => $media->getId()));
  }

  public function getbyidAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'GetById');

    $media = $this->getBusiness()->getById(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId()
    );

    $this->attachMediaUrls($media);

    $this->responseData->setData(new Response($media));
  }
  
  public function getmultiplebyidsAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'GetMultipleByIds');
    
    $medias = $this->getBusiness()->getMultipleByIds(
        $validatedRequest->getIds(),
        $validatedRequest->getWebsiteId()
    );

    foreach ($medias as &$media) {
      $this->attachMediaUrls($media);
    }
    
    $this->responseData->setData(new Response\GetMultipleByIds(
        $validatedRequest->getIds(),
        $medias
    ));
  }

  public function getallAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'GetAll');

    $filterValues = array(
      'albumid' => $validatedRequest->getAlbumId(),
      'type' => $validatedRequest->getType(),
      'maxiconwidth' => $validatedRequest->getMaxIconwidth(),
      'maxiconheight' => $validatedRequest->getMaxIconheight(),
      'limit' => $validatedRequest->getLimit(),
      'start' => $validatedRequest->getStart(),
      'sort' => $validatedRequest->getSort(),
      'direction' => $validatedRequest->getDirection(),
      'search' => $validatedRequest->getSearch(),
    );

    $medias = $this->getBusiness()->getAllByWebsiteId(
        $validatedRequest->getWebsiteId(),
        $filterValues
    );

    foreach ($medias as &$media) {
      $this->attachMediaUrls($media);
    }

    $countMedias = $this->getBusiness()->getAllByWebsiteId(
        $validatedRequest->getWebsiteId(),
        $filterValues,
        true
    );

    $this->responseData->setData(new Response\GetAll($medias));
    $this->responseData->getData()->setTotal(count($countMedias));
  }

  public function getAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'GetByFilter');

    $getFilterValues = array(
      'maxiconwidth' => $validatedRequest->getMaxIconwidth(),
      'maxiconheight' => $validatedRequest->getMaxIconheight(),
      'limit' => $validatedRequest->getLimit(),
      'albumid' => $validatedRequest->getAlbumId(),
      'start' => $validatedRequest->getStart(),
      'sort' => $validatedRequest->getSort(),
      'direction' => $validatedRequest->getDirection(),
      'search' => $validatedRequest->getSearch(),
    );

    $medias = $this->getBusiness()->getByWebsiteIdAndFilter(
        $validatedRequest->getWebsiteId(),
        $getFilterValues
    );

    foreach ($medias as &$media) {
      $this->attachMediaUrls($media);
    }

    $countMedias = $this->getBusiness()->getCountMedia(
        $validatedRequest->getWebsiteId(),
        $getFilterValues
    );

    $this->responseData->setData(new Response\GetByFilter($medias));
    $this->responseData->getData()->setTotal($countMedias);
  }

  public function deleteAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'Delete');

    $this->getBusiness()->delete(
        $validatedRequest->getIds(),
        $validatedRequest->getWebsiteId()
    );
    
    $this->responseData->setData(new Response\Delete());

  }

  public function editAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'Edit');

    $editValues = array(
      'name' => $validatedRequest->getName(),
      'albumid' => $validatedRequest->getAlbumId()
    );

    $this->getBusiness()->edit(
        $validatedRequest->getId(),
        $validatedRequest->getWebsiteId(),
        $editValues
    );
  }

  public function batchmoveAction()
  {
    $validatedRequest = $this->getValidatedRequest('Media', 'BatchMove');

    $move = $this->getBusiness()->moveMediasToAlbum(
        $validatedRequest->getAlbumId(),
        $validatedRequest->getWebsiteId(),
        $validatedRequest->getIds()
    );
  }
  
  protected function createOrUpdateItemFromUploadRequest($upload, $validatedRequest)
  {
    $itemValues = array(
      'albumid' => $validatedRequest->getAlbumId(),
      'name' => $validatedRequest->getName(),
      'filename' => $validatedRequest->getUploadFilename(),
      'extension' => $validatedRequest->getUploadFileExtension(),
      'size' => $upload->getFileSize($validatedRequest->getFileInputname()),
      'lastmod' => $validatedRequest->getLastModification(),
      'type' => MediaType::getByExtension($validatedRequest->getUploadFileExtension()),
      'mimetype' => $upload->getMimeType($validatedRequest->getFileInputname()),
    );
   
    if ($validatedRequest->getId() !== null) {
      return $this->getBusiness()->edit($validatedRequest->getId(), $validatedRequest->getWebsiteId(), $itemValues);
    } else {
      return $this->getBusiness()->create($validatedRequest->getWebsiteId(), $itemValues);
    }
  }
  
  protected function setUploadedFileToMediaItem($upload, $media, $uploadFileName)
  {
    $mediaFileService = new MediaFileService(Registry::getConfig()->media->files->directory);
    $destinationDirectory = $mediaFileService->makeMediaWebsiteDirectory($media->getWebsiteid());
    
    $upload->setDestination($destinationDirectory);
    $upload->receive();
    
    $md5HashedFile = $mediaFileService->hashFilename(
        FS::joinPath($destinationDirectory, $uploadFileName),
        $media->getId()
    );
    $md5HashedFilename = basename($md5HashedFile);

    if ($md5HashedFilename !== null) {
      $this->getBusiness()->edit(
          $media->getId(),
          $media->getWebsiteid(),
          array(
          'file' => $md5HashedFilename,
          'dateuploaded' => time(),
          )
      );
    }
  }
  
  protected function checkQuota($validatedRequest)
  {
    $quota = $this->getBusiness()->getQuota();

    if ((int)$validatedRequest->getUploadFilesize() > (int)$quota->getMaxFileSize()) {
      throw new CmsException(210, __METHOD__, __LINE__, array(
        'quota.maxFileSize'       => $quota->convertByteToMiB($quota->getMaxFileSize()),
        'quota.maxSizePerWebsite' => $quota->convertByteToMiB($quota->getMaxSizePerWebsite()),
      ));
    }

    $websiteSize = $this->getBusiness()->getSizeByWebsiteId($validatedRequest->getWebsiteId());
    $websiteSizeAfterUpload = (int)$validatedRequest->getUploadFilesize() + (int)$websiteSize;
    if ((int)$websiteSizeAfterUpload > (int)$quota->getMaxSizePerWebsite()) {
      throw new CmsException(211, __METHOD__, __LINE__, array(
        'quota.maxFileSize'       => $quota->convertByteToMiB($quota->getMaxFileSize()),
        'quota.maxSizePerWebsite' => $quota->convertByteToMiB($quota->getMaxSizePerWebsite()),
      ));
    }
  }

  /**
   * @param \Cms\Data\Media $media
   */
  protected function attachMediaUrls(&$media)
  {
    $config = Registry::getConfig();
    $maxWidth = $config->media->icon->maxWidth;
    $maxHeight = $config->media->icon->maxHeight;

    $mediaInfoStorage = $this->getMediaInfoStorage($media->getWebsiteid());

    $url = $mediaInfoStorage->getUrl($media->getId());
    $downloadUrl = $mediaInfoStorage->getDownloadUrl($media->getId());
    $iconUrl = $mediaInfoStorage->getPreviewUrl($media->getId(), array(array('maxsize', $maxWidth, $maxHeight)));

    $media->setUrl($url);
    $media->setDownloadUrl($downloadUrl);
    $media->setIconUrl($iconUrl);
  }

  /**
   * @param string $websiteId
   * @return ServiceBasedMediaInfoStorage
   */
  protected function getMediaInfoStorage($websiteId)
  {
    $validationHelper = $this->createMediaValidationHelper();
    $cdnUrl = Registry::getConfig()->server->url . '/cdn/get';
    $urlHelper = new CmsCDNMediaUrlHelper(
        $validationHelper,
        $cdnUrl,
        CmsRequestBase::REQUEST_PARAMETER
    );
    $mediaService = $this->getService('Media');
    $mediaDirectory = Registry::getConfig()->media->files->directory;
    $mediaDirectory .= DIRECTORY_SEPARATOR . $websiteId;
    $iconHelper = new SimpleIconHelper(
        Registry::getConfig()->file->types->icon->directory,
        'icon_fallback.png'
    );
    $mediaInfoStorage = new ServiceBasedMediaInfoStorage(
        $websiteId,
        $mediaDirectory,
        $mediaService,
        $urlHelper,
        $iconHelper
    );

    return $mediaInfoStorage;
  }

  /**
   * @return ValidationHelperInterface
   */
  protected function createMediaValidationHelper()
  {
    $mediaCacheBaseDirectory = Registry::getConfig()->media->cache->directory;
    $mediaCache = new MediaCache($mediaCacheBaseDirectory);
    return new SecureFileValidationHelper($mediaCache, true);
  }
}
