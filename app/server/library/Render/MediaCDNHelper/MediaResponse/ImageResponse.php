<?php


namespace Render\MediaCDNHelper\MediaResponse;

use Render\ImageToolFactory\ImageTool;
use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaCache;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaContext;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;
use Render\RequestHelper\HttpRequestInterface;

class ImageResponse extends StreamResponse
{
  /**
   * @var \Render\MediaCDNHelper\MediaCache
   */
  private $mediaCache;
  /**
   * @var \Render\MediaContext
   */
  private $mediaContext;
  /**
   * @var null|callable
   */
  private $outputCallback;
  /**
   * @var null|string
   */
  private $streamFilePath;

  /**
   * @param HttpRequestInterface      $httpRequest
   * @param MediaRequest              $mediaRequest
   * @param MediaInfoStorageItem      $mediaItem
   * @param ValidationHelperInterface $mediaValidationHelper
   * @param MediaContext              $mediaContext
   * @param MediaCache                $mediaCache
   */
  public function __construct(
      HttpRequestInterface $httpRequest,
      MediaRequest $mediaRequest,
      MediaInfoStorageItem $mediaItem,
      ValidationHelperInterface $mediaValidationHelper,
      MediaContext $mediaContext,
      MediaCache $mediaCache
  ) {
    $this->mediaContext = $mediaContext;
    $this->mediaCache = $mediaCache;
    parent::__construct(
        $httpRequest,
        $mediaRequest,
        $mediaItem,
        $mediaValidationHelper
    );
  }

  /**
   * Output the requested media item
   */
  protected function outputMediaItem()
  {
    if ($this->outputFile($this->getStreamFilePath())) {
      return;
    }
    if ($this->outputViaCallback()) {
      return;
    }
    parent::outputMediaItem();
  }

  /**
   * Output the requested media item via image helper
   */
  private function outputViaCallback()
  {
    $outputCallback = $this->outputCallback;
    if (is_callable($outputCallback)) {
      $outputCallback();
      return;
    }
  }

  /**
   * Add content length only if cached file exists
   */
  protected function addContentLengthHeader()
  {
    $this->addContentLengthHeaderFromFile($this->getStreamFilePath());
    // do not call parent method
  }

  /**
   * initializing the output file
   */
  protected function initOutputFile()
  {
    parent::initOutputFile();
    if ($this->hasOperations()) {
      $this->modifyImageAndSetOutput();
    }
  }

  /**
   * Modify image and set output callback
   */
  protected function modifyImageAndSetOutput()
  {
    if ($this->useCacheFileAsStreamFile()) {
      return true;
    }

    try {
      $imageTool = $this->getModifiedImage();
    } catch (\Exception $e) {
      // TODO: error handling
      return false;
    }

    $this->setOutputCallback(function () use ($imageTool) {
      $imageTool->send();
      $imageTool->close();
    });
    $this->setResponseCode(200);
    $this->writeCacheFile($imageTool);
    return true;
  }

  /**
   * if cache file valid set them as stream file
   *
   * @return bool
   */
  protected function useCacheFileAsStreamFile()
  {
    $cacheFilePath = $this->getCacheFilePath();
    if (!is_string($cacheFilePath) || !is_file($cacheFilePath)) {
      return false;
    }
    $orgFileTime = @\filemtime($this->getOriginalFilePath());
    $cacheFileTime = @\filemtime($cacheFilePath);
    $cacheFileSize = @\filesize($cacheFilePath);
    if (empty($orgFileTime) || empty($cacheFileTime) || empty($cacheFileSize)) {
      return false;
    }
    if ($orgFileTime >= $cacheFileTime) {
      return false;
    }
    if ($this->getLastModifiedDataForHeader() >= $cacheFileTime) {
      return false;
    }
    $this->setStreamFilePath($cacheFilePath);
    $this->setResponseCode(200);
    return true;
  }

  /**
   * @param ImageTool $imageTool
   *
   * @return bool
   */
  protected function writeCacheFile(ImageTool $imageTool)
  {
    if (!$this->isWritingCacheFileAllowed()) {
      return true;
    }
    $this->prepareCache();
    $cacheFilePath = $this->getCacheFilePath();
    if (empty($cacheFilePath)) {
      return false;
    }
    if (!$imageTool->save($cacheFilePath)) {
      return false;
    }
    // important: clear the file state cache to get the right values for file calls
    clearstatcache(true, $cacheFilePath);
    $this->setStreamFilePath($cacheFilePath);
    return true;
  }

  /**
   * @return ImageTool
   */
  protected function getModifiedImage()
  {
    $imageTool = $this->getImageTool();
    $imageTool->open($this->getOriginalFilePath());
    $imageTool->modify($this->getOperations());
    return $imageTool;
  }

  /**
   * @return array
   */
  protected function getOperations()
  {
    return $this->getMediaRequest()->getOperations();
  }

  /**
   * @return boolean
   */
  protected function hasOperations()
  {
    return (count($this->getOperations()) > 0);
  }

  /**
   * @return ImageTool
   */
  protected function getImageTool()
  {
    return $this->mediaContext->getImageTool();
  }

  /**
   * @param callable|null $outputCallback
   */
  protected function setOutputCallback($outputCallback)
  {
    $this->outputCallback = $outputCallback;
  }

  /**
   * @return null|string
   */
  protected function getStreamFilePath()
  {
    return $this->streamFilePath;
  }

  /**
   * @param $streamFilePath
   */
  protected function setStreamFilePath($streamFilePath)
  {
    $this->streamFilePath = $streamFilePath;
  }

  /**
   * @return bool
   */
  protected function isWritingCacheFileAllowed()
  {
    return $this->getMediaValidationHelper()->isValidRequest(
        $this->getMediaItem(),
        $this->getMediaRequest()
    );
  }

  /**
   * @return \Render\MediaCDNHelper\MediaCache
   */
  protected function getMediaCache()
  {
    return $this->mediaCache;
  }

  /**
   * @return string
   */
  protected function getCacheFilePath()
  {
    return $this->getMediaCache()->getCacheFilePath(
        $this->getMediaItem(),
        $this->getOperations()
    );
  }

  /**
   * @return string
   */
  protected function prepareCache()
  {
    $mediaCache = $this->getMediaCache();
    $mediaCache->prepareCache($this->getMediaItem());
    return $this->getCacheFilePath();
  }
}
