<?php
namespace Cms\Creator;

use Cms\Render\InfoStorage\ModuleInfoStorage\ServiceBasedModuleInfoStorage;
use Cms\Service\Modul as ModuleService;

/**
 * Class CreatorServiceBasedModuleInfoStorage
 *
 * ServiceBased Data but AssetPath of created page (useful for css/html caching)
 *
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class CreatorServiceBasedModuleInfoStorage extends ServiceBasedModuleInfoStorage
{

  /**
   * @var string
   */
  private $relativePathToWebRoot = '';

  /**
   * @param string        $websiteId
   * @param ModuleService $moduleService
   * @param string        $relativePathToWebRoot
   *
   */
  public function __construct($websiteId, ModuleService $moduleService, $relativePathToWebRoot = '')
  {
    parent::__construct($websiteId, $moduleService);
    $this->relativePathToWebRoot = $relativePathToWebRoot;
  }

  public function getModuleAssetUrl($moduleId)
  {
    return $this->relativePathToWebRoot . 'files/assets/modules/' . $moduleId;
  }
}
