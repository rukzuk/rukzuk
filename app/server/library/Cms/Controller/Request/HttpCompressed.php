<?php
namespace Cms\Controller\Request;

use Cms\Exception as CmsException;

/**
 * Http-Request mit Client zu Server Komprimierung
 *
 * @package    Cms
 * @subpackage Controller
 */
class HttpCompressed extends \Zend_Controller_Request_Http
{
    protected static $postDataDecompressed = false;

    public function decompressRequest()
    {
    if ($this->isPost()) {
      if (self::$postDataDecompressed !== true) {
        $compression = $compressionPostBody = $this->getHeader('X-cms-encoding');
        if (empty($compression)) {
          $compression = $this->getPost('X-cms-encoding');
        }
        if (!empty($compression)) {
          $charMarker = null;
          if (preg_match('/^(.*)-(.*)$/', $compression, $matches)) {
            $compression = $matches[1];
            $charMarker = $matches[2];
          }
          try {
            switch($compression)
            {
              case 'gz':
                  $this->decompressPostGz(!empty($compressionPostBody), $charMarker);
                  self::$postDataDecompressed = true;
                    break;
            }
          } catch (\Exception $e) {
          // Verarbeitung abbrechen
            throw new CmsException(
                2,
                __METHOD__,
                __LINE__,
                array('message' => 'error at decompression request data: '.$e->getMessage()),
                null,
                400
            );
          }
            
          if (self::$postDataDecompressed !== true) {
            throw new CmsException(
                2,
                __METHOD__,
                __LINE__,
                array('message' => 'request data compression not supported'),
                null,
                400
            );
          }
        }
      }
    }
    }

    protected function decompressPostGz($postBody, $charMarker)
    {
      if ($postBody) {
        $this->decompressPostBodyGz($charMarker);
      } else {
        $this->decompressPostVarsGz($charMarker);
      }
    }

    protected function decompressPostBodyGz($charMarker)
    {
      $body = $this->getRawBody();
      $this->decompressGz($body, $charMarker);
      $requestParams = \Seitenbau\Json::decode($body, \Zend_Json::TYPE_ARRAY);
      if (is_array($requestParams)) {
        foreach (array_keys($requestParams) as $nextParamName) {
          $_POST[$nextParamName] = $requestParams[$nextParamName];
        }
      }
    }

    protected function decompressPostVarsGz($charMarker)
    {
      foreach ($_POST as $nextKey => &$nextValue) {
        if (substr($nextKey, 0, 2) != 'X-') {
          $this->decompressGz($nextValue, $charMarker);
        }
      }
    }
    

    protected function decompressGz(&$value, $charMarker = null)
    {
      $charMarkerRegExp = null;
      if (!empty($charMarker)) {
        $charMarkerRegExp = '/[\x'.dechex($charMarker).']([\x'.dechex($charMarker).'-\xff])/';
      }
      if (isset($value) && is_string($value) && strlen($value) > 0) {
        $deflateValue = utf8_decode($value);
        if ($charMarkerRegExp) {
          $deflateValue = preg_replace_callback(
              $charMarkerRegExp,
              function ($match) use ($charMarker) {
                return chr(ord($match[1])-$charMarker);
              },
              $deflateValue
          );
        }
        $deflateValue = @gzinflate($deflateValue);
        if ($deflateValue === false) {
          $err = error_get_last();
          if (!empty($err)) {
            throw new \Exception($err['message']);
          }
        }
        $value = $deflateValue;
      }

    }
}
