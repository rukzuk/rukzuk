<?php


namespace Render\MediaCDNHelper\MediaResponse;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;
use Render\RequestHelper\HttpRequestInterface;

class StreamResponse implements ResponseInterface
{
  const CHUNK_SIZE = 1048576;
  const HTTP_EXPIRE_SECONDS = 604800; // now + 1 week

  /**
   * @var HttpRequestInterface
   */
  private $httpRequest;
  /**
   * @var MediaRequest
   */
  private $mediaRequest;
  /**
   * @var MediaInfoStorageItem
   */
  private $mediaItem;
  /**
   * @var ValidationHelperInterface
   */
  private $mediaValidationHelper;
  /**
   * @var int
   */
  private $responseCode = 500;
  /**
   * @var array
   */
  private $headers = array();
  /**
   * @var bool
   */
  private $fileOutputEnabled = true;

  /**
   * @param HttpRequestInterface      $httpRequest
   * @param MediaRequest              $mediaRequest
   * @param MediaInfoStorageItem      $mediaItem
   * @param ValidationHelperInterface $mediaValidationHelper
   */
  public function __construct(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem,
      ValidationHelperInterface $mediaValidationHelper
  ) {
    $this->httpRequest = $httpRequest;
    $this->mediaRequest = $mediaRequest;
    $this->mediaItem = $mediaItem;
    $this->mediaValidationHelper = $mediaValidationHelper;
    $this->init();
  }

  /**
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
  final public function outputBody()
  {
    if (!$this->fileOutputEnabled()) {
      return;
    }
    $this->outputMediaItem();
  }

  protected function outputMediaItem()
  {
    $this->outputFile($this->getOriginalFilePath());
  }

  protected function init()
  {
    if (!$this->isRequestCacheValid()) {
      $this->initOutputFile();
    }
    $this->initHeader();
  }

  /**
   * initializing the output file
   */
  protected function initOutputFile()
  {
    $this->enableFileOutput();
    $this->setResponseCode(200);
  }

  protected function initHeader()
  {
    $this->addLastModifiedHeader();
    $this->addExpireHeader();
    $this->addCacheControlHeader();
    $this->addContentTypeHeader();
    $this->addHttpCacheHeader();
    if ($this->fileOutputEnabled()) {
      $this->addContentLengthHeader();
      $this->addContentDispositionHeader();
    }
  }

  protected function addLastModifiedHeader()
  {
    $this->addHeader(
        'Last-Modified',
        gmdate('D, d M Y H:i:s T', $this->getLastModifiedDataForHeader())
    );
  }

  protected function addExpireHeader()
  {
    if (self::HTTP_EXPIRE_SECONDS !== false && self::HTTP_EXPIRE_SECONDS > 0) {
      $this->addHeader(
          'Expires',
          gmdate('D, d M Y H:i:s T', (time() + self::HTTP_EXPIRE_SECONDS))
      );
      $this->addHeader('Pragma', '');
    }
  }

  protected function addCacheControlHeader()
  {
    $this->addHeader('Cache-Control', 'must-revalidate, private');
  }

  protected function addContentTypeHeader()
  {
    $contentType = $this->getMimeType($this->getOriginalFilePath());
    $this->addHeader('Content-Type', $contentType);
  }

  protected function addContentLengthHeader()
  {
    $this->addContentLengthHeaderFromFile($this->getOriginalFilePath());
  }

  /**
   * @param string $filePath
   */
  protected function addContentLengthHeaderFromFile($filePath)
  {
    if ($this->isValidFile($filePath)) {
      $fileSize = @\filesize($filePath);
      if (!empty($fileSize)) {
        $this->addHeader('Content-Length', $fileSize);
      }
    }
  }

  protected function addHttpCacheHeader()
  {
    if (!$this->isRequestCacheValid()) {
      return;
    }
    $this->disableFileOutput();
    $this->setResponseCode(304);
    $this->addHeader('Content-Length', '0');
  }

  protected function addContentDispositionHeader()
  {
    $this->addHeader(
        'Content-Disposition',
        'inline; filename="' . $this->getFileNameForHeader() . '"'
    );
  }

  /**
   * @return bool
   */
  protected function isRequestCacheValid()
  {
    $ifModifiedSince = strtotime(preg_replace(
        '/;.*$/',
        '',
        $this->getHttpRequest()->getHeader('IF_MODIFIED_SINCE')
    ));
    if ($ifModifiedSince <= 0) {
      return false;
    }
    if ($this->getLastModifiedDataForHeader() != $ifModifiedSince) {
      return false;
    }
    return true;
  }

  /**
   * @return int
   */
  protected function getLastModifiedDataForHeader()
  {
    return $this->getMediaItem()->getLastModified();
  }

  /**
   * @return string
   */
  protected function getFileNameForHeader()
  {
    $filename = preg_replace(
        '/[^0-9a-z_\-+\.]/i',
        '',
        $this->getMediaItem()->getName()
    );
    return utf8_decode($filename);
  }

  /**
   * @param $name
   * @param $value
   */
  protected function addHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

  /**
   * @param int $responseCode
   */
  protected function setResponseCode($responseCode)
  {
    $this->responseCode = $responseCode;
  }

  /**
   * @return boolean
   */
  protected function fileOutputEnabled()
  {
    return $this->fileOutputEnabled;
  }

  protected function enableFileOutput()
  {
    $this->fileOutputEnabled = true;
  }

  protected function disableFileOutput()
  {
    $this->fileOutputEnabled = false;
  }

  /**
   * @param $fileName
   *
   * @return string
   */
  protected function getMimeType($fileName)
  {
    return \Seitenbau\Mimetype::getMimetype($fileName);
  }

  /**
   * @return \Render\RequestHelper\HttpRequestInterface
   */
  protected function getHttpRequest()
  {
    return $this->httpRequest;
  }

  /**
   * @return \Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface
   */
  protected function getMediaValidationHelper()
  {
    return $this->mediaValidationHelper;
  }

  /**
   * @return MediaRequest
   */
  protected function getMediaRequest()
  {
    return $this->mediaRequest;
  }

  /**
   * @return \Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem
   */
  protected function getMediaItem()
  {
    return $this->mediaItem;
  }

  /**
   * @return string
   */
  protected function getOriginalFilePath()
  {
    return $this->getMediaItem()->getFilePath();
  }

  /**
   * @param $filePath
   *
   * @return bool
   */
  protected function isValidFile($filePath)
  {
    if (!is_string($filePath) || empty($filePath)) {
      return false;
    }
    if (!is_file($filePath) || !is_readable($filePath)) {
      return false;
    }
    return true;
  }

  /**
   * @param $filePath
   *
   * @return bool
   */
  protected function outputFile($filePath)
  {
    if (!$this->isValidFile($filePath)) {
      return false;
    }
    if (!($fd = fopen($filePath, 'rb'))) {
      return false;
    }
    while ((!feof($fd)) && (!connection_aborted())) {
      print(fread($fd, self::CHUNK_SIZE));
    }
    fclose($fd);
    return true;
  }
}
