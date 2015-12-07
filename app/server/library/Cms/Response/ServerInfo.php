<?php
namespace Cms\Response;

use Seitenbau\Locale as SbLocale;

/**
 * Response Ergebnis fuer Index info
 *
 * @package      Cms
 * @subpackage   Response\Index
 */
class ServerInfo implements IsResponseData
{
  /**
   * @var string
   */
  public $mode = null;
  /**
   * @var string
   */
  public $maxUploadSize = null;
  /**
   * @var string
   */
  public $urls = null;
  /**
   * @var array
   */
  public $quota = null;
  /**
   * @var string
   */
  public $language = null;
  /**
   * @var string
   */
  public $supportedPublishTypes = array();

  public function __construct($data)
  {
    if (is_array($data)) {
      $this->setValuesFromArray($data);
    }
  }
  
  public function setMode($mode)
  {
    $this->mode = $mode;
  }
  public function setMaxUploadSize($maxUploadSize)
  {
    $this->maxUploadSize = $maxUploadSize;
  }
  public function setUrls($urls)
  {
    $this->urls = $urls;
  }
  public function setQuota($quota)
  {
    $this->quota = $quota;
  }
  public function setLanguage($language)
  {
    if (is_string($language)) {
      $this->language = SbLocale::convertToLanguageCode($language);
    } else {
      $this->language = null;
    }
  }

  /**
   * @param string $supportedPublishTypes
   */
  public function setSupportedPublishTypes($supportedPublishTypes)
  {
    $this->supportedPublishTypes = $supportedPublishTypes;
  }

  private function setValuesFromArray(array $data = array())
  {
    $this->setMode($data['mode']);
    $this->setMaxUploadSize($data['maxUploadSize']);
    $this->setUrls($data['urls']);
    $this->setLanguage($data['language']);
    $this->setQuota($data['quota']);
    $this->setSupportedPublishTypes($data['supportedPublishTypes']);
  }
}
