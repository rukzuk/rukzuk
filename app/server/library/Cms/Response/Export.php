<?php
namespace Cms\Response;

/**
 * Export
 *
 * @package      Cms
 * @subpackage   Response
 */
class Export
{
  /**
   * @var string
   */
  public $url;
  /**
   * @param string $cdnExportUrl
   */
  public function __construct($cdnExportUrl)
  {
    if ($cdnExportUrl !== null) {
      $this->setUrl($cdnExportUrl);
    }
  }
  /**
   * @param string $url
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
}
