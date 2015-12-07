<?php


namespace Render\Nodes;

use Render\APIs\LegacyModuleAPI;
use Render\LegacyModule;
use Render\ModuleInfo;
use Render\RenderContext;
use Render\Unit;
use Render\Visitors\AbstractVisitor;
use Render\NodeTree as NodeTree;

class LegacyNode extends AbstractRenderNode
{

  /**
   * @var LegacyModule
   */
  private $module;

  /**
   * @var null|LegacyModuleAPI
   */
  private $moduleApi;

  /**
   * @var \Render\ModuleInfo
   */
  private $moduleInfo;

  /**
   * @param Unit               $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @param LegacyModule       $module
   * @param string             $parentId
   * @param NodeTree           $relatedTree
   */
  public function __construct(
      Unit $unit,
      ModuleInfo $moduleInfo,
      LegacyModule $module,
      $parentId,
      NodeTree $relatedTree
  ) {
    parent::__construct($unit, $moduleInfo, $parentId, $relatedTree);
    $this->module = $module;
  }

  public function accept(AbstractVisitor $visitor)
  {
    return $visitor->visitLegacyNode($this);
  }

  public function renderModuleHead()
  {
    $api = $this->getModuleAPI(null, null);
    $this->getModule()->head($api);
  }

  public function renderUnitCss()
  {
    $api = $this->getModuleAPI(null, null);
    $this->getModule()->css($api);
  }

  public function renderHtmlOutput(AbstractVisitor $apiDefaultVisitor, RenderContext $renderContext)
  {
    $api = $this->getModuleAPI($apiDefaultVisitor, $renderContext);
    $this->getModule()->html($api);
  }

  public function getModuleManifest()
  {
    return $this->getModuleInfo()->getManifest();
  }

  /**
   * @return LegacyModule
   */
  public function getModule()
  {
    return $this->module;
  }

  /**
   * @param $defaultVisitor
   * @param $renderContext
   *
   * @return LegacyModuleAPI
   */
  public function getModuleAPI($defaultVisitor, $renderContext)
  {
    if (!is_object($this->moduleApi)) {
      $this->moduleApi = new LegacyModuleAPI($defaultVisitor, $this, $renderContext);
    }
    $this->moduleApi->setDefaultVisitor($defaultVisitor);
    return $this->moduleApi;
  }
}
