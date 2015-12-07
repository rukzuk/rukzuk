<?php


namespace Render\APIs\APIv1;

use Render\NodeTree;
use Render\RenderContext;
use Render\Unit;
use Render\Visitors\AbstractVisitor;
use Render\Visitors\UnitDataVisitor;

class RenderAPI extends CSSAPI
{

  /**
   * @var AbstractVisitor  Visitor for the current rendering mode
   */
  private $renderingVisitor;

  /**
   * @var EditableHtmlTag
   */
  private $editableHtmlTag;

  /**
   * @param AbstractVisitor $renderingVisitor
   * @param NodeTree        $nodeTree
   * @param RenderContext   $renderContext
   */
  public function __construct(
      AbstractVisitor $renderingVisitor,
      NodeTree $nodeTree,
      RenderContext $renderContext
  ) {
    parent::__construct($nodeTree, $renderContext);
    $this->renderingVisitor = $renderingVisitor;
  }

  /**
   * Returns a map list of all provided unit data
   *
   * @param null|\Render\Unit $unit
   *
   * @return array
   */
  public function getAllUnitData(Unit $unit = null)
  {
    if (is_null($unit)) {
      $startNode = $this->getNodeTree()->getRootNode();
    } else {
      $startNode = $this->getNodeByUnitId($unit->getId());
    }

    if (method_exists($startNode, 'getUnitId')) {
      $cacheKey = 'renderAPI.getAllUnitData.'.$startNode->getUnitId();
      $cache = $this->getRenderContext()->getCache();
      $allUnitDataCache = $cache->getTemporaryValue($cacheKey);
      if (is_array($allUnitDataCache)) {
        return $allUnitDataCache;
      }
    }

    $unitDataVisitor = new UnitDataVisitor($this->getRenderContext());
    $startNode->accept($unitDataVisitor);
    $allUnitData = $unitDataVisitor->getUnitData();

    if (isset($cache) && !empty($cacheKey)) {
      $cache->setTemporaryValue($cacheKey, $allUnitData);
    }

    return $allUnitData;
  }

  /**
   * Triggers the rendering of the given unit with the current renderer
   *
   * @param Unit $unit the unit that should be rendered with the current renderer
   *
   * @return void
   */
  public function renderUnit(Unit $unit)
  {
    $node = $this->getNodeByUnitId($unit->getId());
    $node->accept($this->renderingVisitor);
  }

  /**
   * Triggers the rendering of all children units of the given unit
   *
   * @param array|string $unit the unit which children units should be rendered.
   *
   * @return void
   */
  public function renderChildren($unit)
  {
    $children = $this->getChildren($unit);
    foreach ($children as $childUnit) {
      $this->renderUnit($childUnit);
    }
  }

  /**
   * Returns the content for the editable html code.
   * This method creates the tag given by $tag with the html code given
   * by $unit and $key. All links at html code will be fixed.
   * If the current renderings happens outside of the rukzuk cms edit mode,
   * all helper attributes will be removed.
   *
   * @param Unit   $unit       unit that holds the form value
   * @param mixed  $key        key of the requested form value
   * @param string $tag        tag name that will be created around the editable html code
   * @param string $attributes attributes for the created tag
   *
   * @return string
   */
  public function getEditableTag(Unit $unit, $key, $tag, $attributes = '')
  {
    $content = $this->getFormValue($unit, $key, '');
    if (is_null($this->editableHtmlTag)) {
      $this->editableHtmlTag = new EditableHtmlTag($this);
    }
    return $this->editableHtmlTag->getEditableTag($key, $tag, $attributes, $content);
  }
}
