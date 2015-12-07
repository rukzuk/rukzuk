<?php


namespace Render\MediaCDNHelper\MediaResponse;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaContext;
use Render\RequestHelper\HttpRequestInterface;

class MovedResponse implements ResponseInterface
{
  /**
   * @var \Render\RequestHelper\HttpRequestInterface
   */
  private $httpRequest;
  /**
   * @var \Render\MediaCDNHelper\MediaRequest
   */
  private $mediaRequest;
  /**
   * @var \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem
   */
  private $mediaItem;
  /**
   * @var \Render\MediaContext
   */
  private $mediaContext;
  /**
   * Set server error (500) as default
   *
   * @var int
   */
  private $responseCode = 500;
  /**
   * @var array
   */
  private $headers = array();

  /**
   * @param HttpRequestInterface                $httpRequest
   * @param \Render\MediaCDNHelper\MediaRequest $mediaRequest
   * @param MediaInfoStorageItem                $mediaItem
   * @param MediaContext                        $mediaContext
   */
  public function __construct(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem,
      MediaContext $mediaContext
  ) {
    $this->httpRequest = $httpRequest;
    $this->mediaRequest = $mediaRequest;
    $this->mediaItem = $mediaItem;
    $this->mediaContext = $mediaContext;
    $this->initHeaders();
  }

  /**
   * Returns the http status code
   *
   * @return int
   */
  public function getResponseCode()
  {
    return $this->responseCode;
  }

  /**
   * Returns the http headers
   *
   * @return array
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * Output the requested media item
   */
  public function outputBody()
  {
    // do nothing
  }

  protected function initHeaders()
  {
    $newUrl = $this->getNewUrl();
    if (empty($newUrl) || $this->httpRequest->getUri() == $newUrl) {
      // Set infinity loop (508)
      $this->responseCode = 508;
      return;
    }
    $this->responseCode = 301;
    $this->headers['Location'] = $newUrl;
  }

  /**
   * @return null|string
   */
  protected function getNewUrl()
  {
    $mediaRequest = $this->mediaRequest;
    $infoStorage = $this->mediaContext->getMediaInfoStorage();
    switch ($mediaRequest->getCdnType()) {
      case MediaRequest::TYPE_DOWNLOAD:
            return $infoStorage->getDownloadUrl($mediaRequest->getMediaId());
        break;
      case MediaRequest::TYPE_STREAM:
            return $infoStorage->getUrl($mediaRequest->getMediaId());
        break;
      case MediaRequest::TYPE_IMAGE:
            return $infoStorage->getImageUrl(
                $mediaRequest->getMediaId(),
                $mediaRequest->getOperations()
            );
        break;
      case MediaRequest::TYPE_ICON:
            return $infoStorage->getIconUrl(
                $mediaRequest->getMediaId(),
                $mediaRequest->getOperations()
            );
        break;
      case MediaRequest::TYPE_PREVIEW:
            return $infoStorage->getPreviewUrl(
                $mediaRequest->getMediaId(),
                $mediaRequest->getOperations()
            );
        break;
      default:
            return $infoStorage->getUrl($mediaRequest->getMediaId());
        break;
    }
    return null;
  }
}
