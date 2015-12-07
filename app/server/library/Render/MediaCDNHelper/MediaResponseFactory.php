<?php


namespace Render\MediaCDNHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItemDoesNotExists;
use Render\MediaCDNHelper\Exceptions\MediaItemFileNotExists;
use Render\MediaCDNHelper\Exceptions\OldMediaRequest;
use Render\MediaCDNHelper\MediaResponse\DownloadResponse;
use Render\MediaCDNHelper\MediaResponse\ErrorResponse;
use Render\MediaCDNHelper\MediaResponse\IconResponse;
use Render\MediaCDNHelper\MediaResponse\ImageResponse;
use Render\MediaCDNHelper\MediaResponse\MovedResponse;
use Render\MediaCDNHelper\MediaResponse\StreamResponse;
use Render\MediaCDNHelper\MediaResponse\NotFoundResponse;
use Render\MediaContext;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;
use Render\RequestHelper\HttpRequestInterface;

class MediaResponseFactory
{
  /**
   * @var MediaContext
   */
  private $mediaContext;
  /**
   * @var MediaCache
   */
  private $mediaCache;
  /**
   * @var ValidationHelperInterface
   */
  private $mediaValidationHelper;

  /**
   * @param MediaContext              $mediaContext
   * @param MediaCache                $mediaCache
   * @param ValidationHelperInterface $mediaValidationHelper
   */
  public function __construct(
      MediaContext $mediaContext,
      MediaCache $mediaCache,
      ValidationHelperInterface $mediaValidationHelper
  ) {

    $this->mediaContext = $mediaContext;
    $this->mediaCache = $mediaCache;
    $this->mediaValidationHelper = $mediaValidationHelper;
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   *
   * @return ResponseInterface
   */
  public function createResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest
  ) {
    try {
      $mediaItem = $this->getMediaItem($mediaRequest);
      $this->validateRequest($mediaRequest, $mediaItem);
      switch ($mediaRequest->getCdnType()) {
        case MediaRequest::TYPE_IMAGE:
              return $this->getImageResponse($httpRequest, $mediaRequest, $mediaItem);
        case MediaRequest::TYPE_PREVIEW:
              return $this->getPreviewResponse($httpRequest, $mediaRequest, $mediaItem);
        case MediaRequest::TYPE_ICON:
              return $this->getIconResponse($httpRequest, $mediaRequest, $mediaItem);
        case MediaRequest::TYPE_DOWNLOAD:
              return $this->getDownloadResponse($httpRequest, $mediaRequest, $mediaItem);
        default:
              return $this->getStreamResponse($httpRequest, $mediaRequest, $mediaItem);
      }
    } catch (MediaInfoStorageItemDoesNotExists $ignore) {
      return new NotFoundResponse();
    } catch (MediaItemFileNotExists $ignore) {
      return new NotFoundResponse();
    } catch (OldMediaRequest $ignore) {
      return $this->getMovedResponse($httpRequest, $mediaRequest, $mediaItem);
    } catch (\Exception $ignore) {
      return new ErrorResponse();
    }
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return StreamResponse
   */
  protected function getStreamResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    return new StreamResponse(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $this->getMediaValidationHelper()
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return DownloadResponse
   */
  protected function getDownloadResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    return new DownloadResponse(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $this->getMediaValidationHelper()
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return ImageResponse
   */
  protected function getImageResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    return new ImageResponse(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $this->getMediaValidationHelper(),
        $this->getMediaContext(),
        $this->getMediaCache()
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return IconResponse
   */
  protected function getIconResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    return new IconResponse(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $this->getMediaValidationHelper(),
        $this->getMediaContext(),
        $this->getMediaCache()
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return ImageResponse|IconResponse
   */
  protected function getPreviewResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    switch (true){
      case $this->isTypeOfSvg($mediaItem):
            return $this->getStreamResponse($httpRequest, $mediaRequest, $mediaItem);
        break;
      case $this->isTypeOfImage($mediaItem):
            return $this->getImageResponse($httpRequest, $mediaRequest, $mediaItem);
        break;
      default:
            return $this->getIconResponse($httpRequest, $mediaRequest, $mediaItem);
        break;
    }
  }

  /**
   * @param HttpRequestInterface $httpRequest
   * @param MediaRequest         $mediaRequest
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return MovedResponse
   */
  protected function getMovedResponse(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    return new MovedResponse(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $this->getMediaContext()
    );
  }


  protected function validateRequest(
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem
  ) {
    $requestDate = $mediaRequest->getDate();
    if (!empty($requestDate) && $requestDate != $mediaItem->getLastModified()) {
      throw new OldMediaRequest();
    }
    $filePath = $mediaItem->getFilePath();
    if (!is_file($filePath) || !is_readable($filePath)) {
      throw new MediaItemFileNotExists();
    }
  }

  /**
   * @param MediaRequest $mediaRequest
   *
   * @return MediaInfoStorageItem
   */
  protected function getMediaItem(MediaRequest $mediaRequest)
  {
    $infoStorage = $this->getMediaContext()->getMediaInfoStorage();
    return $infoStorage->getItem($mediaRequest->getMediaId());
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return bool
   */
  protected function isTypeOfImage(MediaInfoStorageItem $mediaItem)
  {
    $filePath = $mediaItem->getFilePath();
    return $this->getMediaContext()->getImageTool()->isImageFile($filePath);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return bool
   */
  protected function isTypeOfSvg(MediaInfoStorageItem $mediaItem)
  {
    $filePath = $mediaItem->getFilePath();
    return (substr($filePath, -3) == 'svg');
  }

  /**
   * @return \Render\MediaContext
   */
  protected function getMediaContext()
  {
    return $this->mediaContext;
  }

  /**
   * @return MediaCache
   */
  protected function getMediaCache()
  {
    return $this->mediaCache;
  }

  /**
   * @return ValidationHelperInterface
   */
  protected function getMediaValidationHelper()
  {
    return $this->mediaValidationHelper;
  }
}
