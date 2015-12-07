<?php


namespace Render\Nodes;

use Render\ModuleInfo;
use Render\Unit;
use \Render\Visitors\AbstractVisitor;
use \Render\NodeTree;

/**
 * This class only describes the Node tree structure.
 * It does not and should not handle any module related information!
 *
 * @package Render\Nodes
 */
abstract class AbstractRenderNode implements INode
{

  /**
   * @var Unit
   */
  private $unit;

  /**
   * @var \Render\ModuleInfo
   */
  private $moduleInfo;

  /**
   * @var INode[]
   */
  private $children = array();

  /**
   * @var string
   */
  private $parentId;

  /**
   * @var \Render\NodeTree
   */
  private $relatedTree;

  /**
   * Part of the visitor pattern to realize a double dispatching. It is used
   * to tell the visitor what kind of Node/Module the current Node is.
   *
   * Note: This is the only function that the visitor pattern really need
   * for all nodes!
   *
   * @param AbstractVisitor $visitor
   * @return Nothing
   */
  // The following function declaration throw fatal error at php 5.3.3, because the function
  // is previously declared at Node interface. If someone removes the declaration at Node interface,
  // please check if the following declaration has to be uncomment.
  //public abstract function accept(AbstractVisitor $visitor);

  /**
   * @param Unit               $unit        the unit data of the current node
   * @param \Render\ModuleInfo $moduleInfo  the module data of the current node
   * @param string             $parentId    the id of the parent node
   * @param NodeTree           $relatedTree the related NodeTree object
   */
  public function __construct(
      Unit $unit,
      ModuleInfo $moduleInfo,
      $parentId,
      NodeTree $relatedTree
  ) {
    $this->unit = $unit;
    $this->moduleInfo = $moduleInfo;
    $this->parentId = $parentId;
    $this->relatedTree = $relatedTree;
  }

  /**
   * Returns the parent node of the current node or null when there is no parent.
   *
   * @return AbstractRenderNode|null
   */
  public function getParent()
  {
    $unitMap = $this->relatedTree->getUnitMap();
    $parentId = $this->getParentId();
    if (isset($parentId)) {
      return $unitMap[$parentId];
    } else {
      return null;
    }
  }

  /**
   * Returns the related NodeTree of the current node
   *
   * @return NodeTree
   */
  public function getTree()
  {
    return $this->relatedTree;
  }

  /**
   * @return string the unitId of this node
   */
  public function getUnitId()
  {
    return $this->unit->getId();
  }

  /**
   * @return string|null the unitId of the parent node or null when there
   */
  public function getParentId()
  {
    return $this->parentId;
  }

  /**
   * @return string the module id of the this node
   */
  public function getModuleId()
  {
    return $this->unit->getModuleId();
  }

  /**
   * @return AbstractRenderNode[]
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Add a single Node to the $children array
   *
*@param INode $node
   */
  public function addChild(INode $node)
  {
    $this->children[] = $node;
  }

  /**
   * @return Unit
   */
  public function &getUnit()
  {
    return $this->unit;
  }

  /**
   * @return \Render\ModuleInfo
   */
  public function getModuleInfo()
  {
    return $this->moduleInfo;
  }
}
