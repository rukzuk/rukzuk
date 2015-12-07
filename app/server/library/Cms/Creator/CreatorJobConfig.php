<?php


namespace Cms\Creator;

class CreatorJobConfig
{
  /**
   * @var string
   */
  private $websiteId;
  /**
   * @var array
   */
  private $publishConfig;

  /**
   * @param string $websiteId
   * @param array  $publishConfig
   */
  public function __construct($websiteId, array $publishConfig)
  {
    $this->websiteId = $websiteId;
    $this->publishConfig = $publishConfig;
  }

  /**
   * @return string
   */
  public function getWebsiteId()
  {
    return $this->websiteId;
  }
}
