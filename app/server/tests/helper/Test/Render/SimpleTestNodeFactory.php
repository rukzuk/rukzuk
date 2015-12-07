<?php


namespace Test\Render;

use Render\NodeFactory;

class SimpleTestNodeFactory extends NodeFactory
{
  protected function loadModule($moduleId)
  {
    return new SimpleTestModule($moduleId,
      $this->getModuleInfoStorage()->getModuleAssetPath($moduleId),
      $this->getModuleInfoStorage()->getModuleAssetUrl($moduleId),
      $this->getModuleInfoStorage()->getModuleManifest($moduleId));
  }
}