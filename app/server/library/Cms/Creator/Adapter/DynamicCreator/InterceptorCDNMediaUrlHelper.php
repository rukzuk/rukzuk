<?php
namespace Cms\Creator\Adapter\DynamicCreator;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaUrlHelper\CDNMediaUrlHelper;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;

/**
 * Class InterceptorCDNMediaUrlHelper
 *
 * Remembers calls to Media Items (Image, Icons, Image Previews) including the operations (resize etc.).
 * This is useful to pre-generate them while creating the website
 *
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class InterceptorCDNMediaUrlHelper extends CDNMediaUrlHelper
{

  private $calls = array();

  /**
   * @var callback|callable
   */
  private $interceptorCallable;

  /**
   * @param ValidationHelperInterface $validationHelper
   * @param string                    $cdnUrl
   * @param string                    $requestParameterName
   * @param callback|callable         $interceptorCallable
   */
  public function __construct(
      ValidationHelperInterface $validationHelper,
      $cdnUrl,
      $requestParameterName = self::REQUEST_PARAMETER,
      $interceptorCallable = null
  ) {
    $this->interceptorCallable = $interceptorCallable;
    parent::__construct($validationHelper, $cdnUrl, $requestParameterName);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param                      $filename
   * @param array                $operations
   *
   * @param string               $cdnType
   *
   * @return string
   */
  protected function createImageUrl(MediaInfoStorageItem $mediaItem, $filename, array $operations, $cdnType)
  {

    $call = array(
      'id' => $mediaItem->getId(),
      'type' => $cdnType,
      'operations' => $operations,
    );
    $this->addMediaCall($call);

    return parent::createImageUrl($mediaItem, $filename, $operations, $cdnType);
  }

  private function addMediaCall($call)
  {
    $key = $this->uniqueKey($call);
    $this->calls[$key] = $call;
    if ($this->interceptorCallable) {
      if (is_callable($this->interceptorCallable, true)) {
        call_user_func($this->interceptorCallable, $key, $call);
      }
    }
  }

  public function getMediaUrlCalls()
  {
    return $this->calls;
  }

  private function uniqueKey($array)
  {
    // very simple method to build a set based on an assoc php array
    // md5 could be replace by something faster,
    // but json_encode is very fast compared to concatenation in php (especially for nested arrays)
    return md5(json_encode($array));
  }
}
