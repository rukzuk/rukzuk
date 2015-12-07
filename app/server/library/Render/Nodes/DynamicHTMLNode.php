<?php

namespace Render\Nodes;

use Render\ModuleInterface;
use Render\ModuleInfo;
use Render\NodeTree;
use Render\Unit;
use Render\Visitors\AbstractVisitor;

class DynamicHTMLNode extends AbstractRenderNode
{

  /**
   * @var ModuleInterface
   */
  private $module;

  /**
   * @var string
   */
  private $moduleApiType;

  /**
   * @param Unit                   $unit          the unit data of the current node
   * @param \Render\ModuleInfo     $moduleInfo    the module data of the current node
   * @param string                 $parentId      the id of the parent node
   * @param NodeTree               $relatedTree   the related NodeTree object
   * @param ModuleInterface $module        the module of the current node
   * @param string                 $moduleApiType the API which the module of current node used
   */
  public function __construct(
      Unit $unit,
      ModuleInfo $moduleInfo,
      $parentId,
      NodeTree $relatedTree,
      ModuleInterface $module,
      $moduleApiType
  ) {
    parent::__construct($unit, $moduleInfo, $parentId, $relatedTree);
    $this->module = $module;
    $this->moduleApiType = $moduleApiType;
  }

  /**
   * @return ModuleInterface
   */
  public function getModule()
  {
    return $this->module;
  }

  /**
   * @return string
   */
  public function getModuleApiType()
  {
    return $this->moduleApiType;
  }

  /**
   * Part of the visitor pattern to realize a double dispatching. It is used
   * to tell the visitor what kind of Node/Module the current Node is.
   *
   * Note: This is the only function that the visitor pattern really need
   * for all nodes!
   *
   * @param AbstractVisitor $visitor
   *
   * @return mixed
   */
  public function accept(AbstractVisitor $visitor)
  {
    return $visitor->visitDynamicHTMLNode($this);
  }

  /**
   * Returns the unit data array of the related module.
   *
   * @param $cssApi
   *
   * @return array
   */
  public function provideUnitData($cssApi)
  {
    return $this->getModule()->provideUnitData(
        $cssApi,
        $this->getUnit(),
        $this->getModuleInfo()
    );
  }

  /**
   * Returns the module data array of the related module.
   *
   * @param $headApi
   *
   * @return array
   */
  public function provideModuleData($headApi)
  {
    return $this->getModule()->provideModuleData(
        $headApi,
        $this->getModuleInfo()
    );
  }

  /**
   * Outputs the HTML body elements for this unit using the related module
   * Php code.
   *
   * @param $renderApi
   */
  public function renderHtmlOutput($renderApi)
  {
    $this->getModule()->render(
        $renderApi,
        $this->getUnit(),
        $this->getModuleInfo()
    );
  }

  /**
   * Outputs the CSS code of this unit using the related module Php code.
   *
   * @param $cssApi
   */
  public function renderCssOutput($cssApi)
  {
    $this->getModule()->css(
        $cssApi,
        $this->getUnit(),
        $this->getModuleInfo()
    );
  }
}
