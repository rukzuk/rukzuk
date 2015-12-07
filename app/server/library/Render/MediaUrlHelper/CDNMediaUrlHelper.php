<?php


namespace Render\MediaUrlHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaCDNHelper\MediaRequest;
use Render\MediaUrlHelper\ValidationHelper\ValidationHelperInterface;
use Render\RequestHelper\HttpRequestInterface;

class CDNMediaUrlHelper implements IMediaUrlHelper
{
  const REQUEST_PARAMETER = 'params';

  /**
   * @var
   */
  private $cdnUrl;
  /**
   * @var string
   */
  private $requestParameterName;
  /**
   * @var ValidationHelper\ValidationHelperInterface
   */
  private $validationHelper;

  /**
   * @param ValidationHelperInterface $validationHelper
   * @param string                    $cdnUrl
   * @param string                    $requestParameterName
   */
  public function __construct(
      ValidationHelperInterface $validationHelper,
      $cdnUrl,
      $requestParameterName = self::REQUEST_PARAMETER
  ) {
    $this->cdnUrl = $cdnUrl;
    $this->requestParameterName = $requestParameterName;
    $this->validationHelper = $validationHelper;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return string
   */
  public function getUrl(MediaInfoStorageItem $mediaItem)
  {
    $this->getValidationHelper()->makeStreamRequestValid($mediaItem);
    $requestParameterValues = $this->createRequestParameterValue(
        $mediaItem,
        MediaRequest::TYPE_STREAM
    );
    return $this->createUrl($mediaItem->getName(), $requestParameterValues);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   *
   * @return string
   */
  public function getDownloadUrl(MediaInfoStorageItem $mediaItem)
  {
    $this->getValidationHelper()->makeDownloadRequestValid($mediaItem);
    $requestParameterValues = $this->createRequestParameterValue(
        $mediaItem,
        MediaRequest::TYPE_DOWNLOAD
    );
    return $this->createUrl($mediaItem->getName(), $requestParameterValues);
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   *
   * @return string
   * @throws UnknownOperation
   */
  public function getImageUrl(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->getValidationHelper()->makeImageRequestValid(
        $mediaItem,
        $operations
    );
    return $this->createImageUrl(
        $mediaItem,
        $mediaItem->getName(),
        $operations,
        MediaRequest::TYPE_IMAGE
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param string               $iconFilePath
   * @param array                $operations
   *
   * @return string
   */
  public function getIconUrl(
      MediaInfoStorageItem $mediaItem,
      $iconFilePath,
      array $operations = array()
  ) {
    $this->getValidationHelper()->makeIconRequestValid(
        $mediaItem,
        $operations
    );
    return $this->createImageUrl(
        $mediaItem,
        basename($iconFilePath),
        $operations,
        MediaRequest::TYPE_ICON
    );
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param array                $operations
   *
   * @return string
   * @throws UnknownOperation
   */
  public function getPreviewUrl(
      MediaInfoStorageItem $mediaItem,
      array $operations = array()
  ) {
    $this->getValidationHelper()->makePreviewRequestValid(
        $mediaItem,
        $operations
    );
    return $this->createImageUrl(
        $mediaItem,
        $mediaItem->getName(),
        $operations,
        MediaRequest::TYPE_PREVIEW
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   *
   * @return MediaRequest
   */
  public function getMediaRequest(HttpRequestInterface $httpRequest)
  {
    $params = $this->getValuesFromRequest($httpRequest);
    $operations = $this->convertChainToOperations($params['chain']);
    return new MediaRequest(
        $params['mediaId'],
        $params['cdnType'],
        $params['lastModified'],
        $params['websiteId'],
        $operations
    );
  }

  /**
   * @param HttpRequestInterface $httpRequest
   *
   * @return array
   */
  protected function getValuesFromRequest(HttpRequestInterface $httpRequest)
  {
    $values = array(
      'websiteId' => null,
      'mediaId' => null,
      'cdnType' => MediaRequest::TYPE_STREAM,
      'lastModified' => 0,
      'chain' => '',
    );
    $paramsAsJson = $httpRequest->getParam($this->requestParameterName);
    $params = \json_decode($paramsAsJson, true);
    if (!is_array($params)) {
      return $values;
    }
    $params = array_change_key_case($params, CASE_LOWER);
    if (isset($params['websiteid'])) {
      $values['websiteId'] = $params['websiteid'];
    }
    if (isset($params['id'])) {
      $values['mediaId'] = $params['id'];
    }
    if (isset($params['type'])) {
      $values['cdnType'] = $params['type'];
    }
    if (isset($params['date'])) {
      $values['lastModified'] = $params['date'];
    }
    if (isset($params['chain'])) {
      $values['chain'] = $params['chain'];
    }
    return $values;
  }

  /**
   * Returns a cleaned url conform file name
   *
   * @param $filename
   *
   * @return mixed
   */
  protected function cleanFileNameForUrl($filename)
  {
    return urlencode(preg_replace('/[^0-9a-z_\-+\.]/i', '', $filename));
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param string               $cdnType
   * @param array                $operations
   *
   * @return string
   */
  protected function createRequestParameterValue(
      MediaInfoStorageItem $mediaItem,
      $cdnType,
      array $operations = array()
  ) {
    $params = array(
      'id' => $mediaItem->getId(),
      'type' => $cdnType,
      'date' => $mediaItem->getLastModified(),
    );
    if (!empty($operations)) {
      $params['chain'] = $this->convertOperationsToChainString($operations);
    }
    return urlencode(\json_encode($params));
  }

  /**
   * @param $filename
   * @param $requestParameterValues
   *
   * @return string
   */
  protected function createUrl($filename, $requestParameterValues)
  {
    return sprintf(
        '%s?%s=%s&%s',
        $this->cdnUrl,
        $this->requestParameterName,
        $requestParameterValues,
        $this->cleanFileNameForUrl($filename)
    );
  }

  /**
   * @param array $operations
   *
   * @return string
   * @throws UnknownOperation
   */
  protected function convertOperationsToChainString(array $operations)
  {
    $chain = array();
    foreach ($operations as $operation) {
      switch ($operation[0]) {
        case 'crop':
          $chain[] = sprintf(
              'c%d_%d_%d_%d',
              $operation[1],
              $operation[2],
              $operation[3],
              $operation[4]
          );
              break;
        case 'resize':
          $chain[] = sprintf(
              'r%d_%d_t%d',
              $operation[1],
              $operation[2],
              $operation[3]
          );
              break;
        case 'maxsize':
          $chain[] = sprintf(
              'max%d_%d',
              $operation[1],
              $operation[2]
          );
              break;
        case 'quality':
          $chain[] = sprintf('q%d', $operation[1]);
              break;
        case 'interlace':
          $chain[] = sprintf('i%d', $operation[1]);
              break;
        default:
              throw new UnknownOperation();
          break;
      }
    }
    return implode('.', $chain);
  }

  /**
   * @param string $chainString
   *
   * @return array
   */
  protected function convertChainToOperations($chainString)
  {
    $operations = array();
    $chainParts = explode('.', $chainString);
    foreach ($chainParts as $part) {
      // crop
      if (preg_match('/^c(-?\d+)_(-?\d+)_(\d+)_(\d+)/i', $part, $params)) {
        $operations[] = array(
          'crop',
          intval($params[1]), intval($params[2]),
          intval($params[3]), intval($params[4])
        );
      } elseif (preg_match('/^r(\d+)_(\d+)_t(\d)/i', $part, $params)) {
        $operations[] = array(
          'resize',
          intval($params[1]), intval($params[2]),
          intval($params[3])
        );
      } // max size
      elseif (preg_match('/^max(\d+)_(\d+)/i', $part, $params)) {
        $operations[] = array(
          'maxsize',
          intval($params[1]), intval($params[2])
        );
      } // quality
      elseif (preg_match('/^q(\d+)/i', $part, $params)) {
        $operations[] = array(
          'quality',
          intval($params[1])
        );
      } // interlace
      elseif (preg_match('/^i(\d+)/i', $part, $params)) {
        $operations[] = array(
          'interlace',
          intval($params[1])
        );
      } // legacy resize
      elseif (preg_match('/^r(\d+)_(\d+)(_b(\d)(_p(\d))?)?/i', $part, $params)) {
        if (intval($params[1]) <= 0 || intval($params[2]) <= 0) {
          $resizeType = 3; // resize scale
        } elseif (isset($params[4]) && $params[4] == '1') {
          $resizeType = 2; // resize and fill
        } elseif (isset($params[6]) && $params[6] == '1') {
          $resizeType = 1; // resize center
        } else {
          $resizeType = 0; // resize stretch
        }
        $operations[] = array(
          'resize',
          intval($params[1]), intval($params[2]),
          $resizeType
        );
      }
    }
    return $operations;
  }

  /**
   * @param MediaInfoStorageItem $mediaItem
   * @param                      $filename
   * @param array                $operations
   * @param string               $cdnType
   *
   * @return string
   */
  protected function createImageUrl(MediaInfoStorageItem $mediaItem, $filename, array $operations, $cdnType)
  {
    $requestParameterValues = $this->createRequestParameterValue(
        $mediaItem,
        $cdnType,
        $operations
    );
    return $this->createUrl($filename, $requestParameterValues);
  }

  /**
   * @return string
   */
  protected function getCdnUrl()
  {
    return $this->cdnUrl;
  }

  /**
   * @return string
   */
  protected function getRequestParameterName()
  {
    return $this->requestParameterName;
  }

  /**
   * @return ValidationHelperInterface
   */
  protected function getValidationHelper()
  {
    return $this->validationHelper;
  }
}
