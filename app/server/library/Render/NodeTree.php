<?php


namespace Render;

use Render\Nodes\INode;
use Render\Exceptions\NodeNotFoundException;

class NodeTree
{

  private $rootNode;

  private $unitMap = array();

  private $usedModuleIds = array();

  public function __construct(array &$content, NodeFactory $nodeFactory)
  {
    $unitMap = array();
    $usedModuleIds = array();
    $rootNode = $nodeFactory->createNodeWithSubNodes($content, $unitMap, $usedModuleIds, $this);
    $this->setRootNode($rootNode);
    $this->setUnitMap($unitMap);
    $this->setUsedModuleIds(array_unique($usedModuleIds));
  }

  /**
   * @return INode -- The root node of the page/template tree
   */
  public function getRootNode()
  {
    return $this->rootNode;
  }

  /**
   * @return array -- A map (UnitId -> Node) with all node objects of the tree;
   */
  public function &getUnitMap()
  {
    return $this->unitMap;
  }

  /**
   * @return array
   */
  public function getUsedModuleIds()
  {
    return $this->usedModuleIds;
  }

  /**
   * Returns the Node object for a given unitId
   *
   * @param $unitId
   *
   * @return INode
   * @throws Exceptions\NodeNotFoundException
   */
  public function getNodeByUnitId($unitId)
  {
    $unitMap = $this->getUnitMap();
    if (array_key_exists($unitId, $unitMap)) {
      return $unitMap[$unitId];
    }
    throw new NodeNotFoundException();
  }

  /**
   * @param INode $rootNode
   */
  protected function setRootNode(INode $rootNode)
  {
    $this->rootNode = $rootNode;
  }

  /**
   * @param array $unitMap
   */
  protected function setUnitMap(array &$unitMap)
  {
    $this->unitMap =& $unitMap;
  }

  /**
   * @param array $usedModuleIds
   */
  protected function setUsedModuleIds(array $usedModuleIds)
  {
    $this->usedModuleIds = $usedModuleIds;
  }
}
