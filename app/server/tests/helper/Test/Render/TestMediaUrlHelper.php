<?php


namespace Test\Render;


use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaUrlHelper\IMediaUrlHelper;
use Render\RequestHelper\HttpRequestInterface;

class TestMediaUrlHelper implements IMediaUrlHelper
{
  private $calls = array();

  private $prefix;

  public function __construct($prefix)
  {
    $this->prefix = $prefix;
  }

  public function getUrl(MediaInfoStorageItem $mediaItem)
  {
    $return = $this->prefix . "/" . $mediaItem->getId() . '/'
            . $mediaItem->getName();
    $this->calls[] = array(
      'method' => 'getUrl',
      'params' => array($mediaItem->getId()),
      'return' => $return);
    return $return;
  }

  public function getDownloadUrl(MediaInfoStorageItem $mediaItem)
  {
    $return = $this->prefix . "/download/" . $mediaItem->getId() . '/'
            . $mediaItem->getName();
    $this->calls[] = array(
      'method' => 'getDownloadUrl',
      'params' => array($mediaItem->getId()),
      'return' => $return);
    return $return;
  }

  public function getImageUrl(MediaInfoStorageItem $mediaItem,
                              array $operations = array())
  {
    $ops = array();
    foreach ($operations as $op) {
      $ops[] = join('_', $op);
    }
    $operationSting = join('/', $ops);
    $return = $this->prefix . "/" . $mediaItem->getId() . '/'
            . $mediaItem->getName() . '/' . $operationSting;
    $this->calls[] = array(
      'method' => 'getImageUrl',
      'params' => array($mediaItem->getId(), $operations),
      'return' => $return);
    return $return;
  }

  public function getIconUrl(MediaInfoStorageItem $mediaItem, $iconFilePath,
                             array $operations = array())
  {
    $ops = array();
    foreach ($operations as $op) {
      $ops[] = join('_', $op);
    }
    $operationSting = join('/', $ops);
    $return = $this->prefix . "/icon/" . $mediaItem->getId() . '/'
            . $mediaItem->getName() . '/' . $operationSting;
    $this->calls[] = array(
      'method' => 'getIconUrl',
      'params' => array($mediaItem->getId(), $iconFilePath, $operations),
      'return' => $return);
    return $return;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   *
   * @return string
   */
  public function getPreviewUrl(MediaInfoStorageItem $mediaItem,
                                array $operations = array())
  {
    $ops = array();
    foreach ($operations as $op) {
      $ops[] = join('_', $op);
    }
    $operationSting = join('/', $ops);
    $return = $this->prefix . "/" . $mediaItem->getId() . '/'
      . $mediaItem->getName() . '/' . $operationSting;
    $this->calls[] = array(
      'method' => 'getPreviewUrl',
      'params' => array($mediaItem->getId(), $operations),
      'return' => $return);
    return $return;
  }

  /**
   * @param HttpRequestInterface $httpRequest
   *
   * @return MediaRequest
   */
  public function getMediaRequest(HttpRequestInterface $httpRequest)
  {
    $mediaRequest = new MediaRequest('mediaId', 'cdnType', 1, 'websiteId',
     array(), false);
    $this->calls[] = array(
      'method' => 'getMediaRequest',
      'params' => array($httpRequest),
      'return' => $mediaRequest);
    return $mediaRequest;
  }

  public function getCalls()
  {
    return $this->calls;
  }
}