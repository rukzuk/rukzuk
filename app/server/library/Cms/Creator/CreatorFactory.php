<?php


namespace Cms\Creator;

use Seitenbau\Log;
use Seitenbau\Registry;
use Cms\Creator\Adapter\AbstractCreator;
use Cms\Exception as CmsException;

class CreatorFactory
{
  /**
   * @param string         $creatorName
   * @param CreatorContext $creatorContext
   *
   * @return AbstractCreator
   */
  public function createCreator(
      CreatorContext $creatorContext,
      $creatorName = null
  ) {
    if (is_null($creatorName)) {
      $creatorName = $this->getCreatorName();
    }
    $creatorClassName = self::getCreatorClassName($creatorName);
    $creatorConfig = $this->getCreatorConfig($creatorName);
    return new $creatorClassName($creatorContext, $creatorConfig);
  }

  /**
   * @return string
   */
  protected function getCreatorName()
  {
    $creatorName = Registry::getConfig()->creator->defaultCreator;
    if (empty($creatorName)) {
      return 'dynamic';
    } else {
      return $creatorName;
    }
  }

  /**
   * @param $creatorName
   *
   * @return bool
   */
  public static function creatorExists($creatorName)
  {
    try {
      self::getCreatorClassName($creatorName);
      return true;
    } catch (\Exception $ignore) {
      return false;
    }
  }

  /**
   * @param string $creatorName
   *
   * @return string
   * @throws CmsException
   */
  protected static function getCreatorClassName($creatorName)
  {
    $fullCreatorName = ucfirst($creatorName).'Creator';
    $creatorClassName = 'Cms\Creator\Adapter\\'.$fullCreatorName;
    $creatorFileName = __DIR__.'/Adapter/'.$fullCreatorName.'.php';
    if (file_exists($creatorFileName)) {
      if (class_exists($creatorClassName)) {
        return $creatorClassName;
      }
    }
    Registry::getLogger()->log(
        __METHOD__,
        __LINE__,
        'Class ' . $creatorClassName . ' does not exist',
        Log::ERR
    );
    throw new CmsException('1', __METHOD__, __LINE__);
  }

  /**
   * @param $creatorName
   *
   * @return array
   */
  protected function getCreatorConfig($creatorName)
  {
    $configKeyName = strtolower($creatorName);
    $config = Registry::getConfig()->creator;
    $workingDirectory = $config->directory;

    if (isset($config->{$configKeyName})) {
      $creatorConfig = $config->{$configKeyName}->toArray();
    } else {
      $creatorConfig = array();
    }

    return new CreatorConfig($workingDirectory, $creatorConfig);
  }
}
