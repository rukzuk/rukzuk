<?php


namespace Cms\Creator;

class CreatorConfig
{
  /**
   * @var string
   */
  private $workingDirectory;
  /**
   * @var array
   */
  private $config;

  /**
   * @param string  $workingDirectory
   * @param array   $config
   */
  public function __construct($workingDirectory, array $config)
  {

    $this->workingDirectory = $workingDirectory;
    $this->config = $config;
  }

  /**
   * @return string
   */
  public function getWorkingDirectory()
  {
    return $this->workingDirectory;
  }

  /**
   * @return array
   */
  public function getConfig()
  {
    return $this->config;
  }
}
