<?php


namespace Cms\Creator\Adapter;

use Cms\Creator\CreatorConfig;
use Cms\Creator\CreatorContext;
use Cms\Creator\CreatorJobConfig;

abstract class AbstractCreator
{
  /**
   * @var CreatorContext
   */
  private $creatorContext;

  /**
   * @var CreatorConfig
   */
  private $creatorConfig;

  /**
   * @param CreatorContext $creatorContext
   * @param CreatorConfig  $creatorConfig
   */
  public function __construct(
      CreatorContext $creatorContext,
      CreatorConfig $creatorConfig
  ) {
    $this->creatorContext = $creatorContext;
    $this->creatorConfig = $creatorConfig;
    $this->init();
  }

  /**
   * @return CreatorContext
   */
  protected function getCreatorContext()
  {
    return $this->creatorContext;
  }

  /**
   * @return CreatorConfig
   */
  protected function getCreatorConfig()
  {
    return $this->creatorConfig;
  }

  /**
   * initialize creator
   */
  abstract protected function init();

  /**
   * @param CreatorJobConfig $jobConfig
   */
  abstract public function createWebsite(CreatorJobConfig $jobConfig);

  /**
   * @param CreatorJobConfig $jobConfig
   * @param string           $prepare
   * @param array            $info
   *
   * @return mixed
   */
  abstract public function prepare(CreatorJobConfig $jobConfig, $prepare, array $info);
}
