<?php


namespace Cms\Render\MediaUrlHelper;

use Render\InfoStorage\MediaInfoStorage\MediaInfoStorageItem;
use Render\MediaUrlHelper\CDNMediaUrlHelper;

class CmsCDNMediaUrlHelper extends CDNMediaUrlHelper
{

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
      'websiteid' => $mediaItem->getWebsiteId(),
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
        '%s/%s/%s/%s',
        $this->getCdnUrl(),
        $this->getRequestParameterName(),
        $requestParameterValues,
        $this->cleanFileNameForUrl($filename)
    );
  }
}
