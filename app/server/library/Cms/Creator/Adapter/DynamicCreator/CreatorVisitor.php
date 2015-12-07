<?php


namespace Cms\Creator\Adapter\DynamicCreator;

use Render\Nodes\AbstractRenderNode;
use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\LegacyNode;
use Render\Visitors\AbstractVisitor;

/**
 * Class CreatorVisitor
 *
 * Just looks for the content in the complete NodeTree,
 * also remembers any legacy module and creates list of used modules
 *
 * @package Cms\Creator\Adapter\DynamicCreator
 */
class CreatorVisitor extends AbstractVisitor
{

  /**
   * @var array
   */
  private $usedModuleIds = array();

  /**
   * @var bool
   */
  private $legacySupport = false;

  /**
   * @var array
   */
  private $content = array();


  public function __construct()
  {

  }

  /**
   * @return array
   */
  public function getUsedModuleIds()
  {
    return array_keys($this->usedModuleIds);
  }

  /**
   * @return array
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * @return bool
   */
  public function legacySupportActivated()
  {
    return $this->legacySupport;
  }

  /**
   * @param DynamicHTMLNode $node
   */
  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $this->addModuleToUsedList($node->getModuleId());
    $this->createNodeContent($node);
    $this->visitChildNodes($node);
  }

  /**
   * @param LegacyNode $node
   */
  public function visitLegacyNode(LegacyNode $node)
  {
    $this->addModuleToUsedList($node->getModuleId());
    $this->createNodeContent($node);
    $this->activateLegacySupport();
    $this->visitChildNodes($node);
  }

  /**
   * @param AbstractRenderNode $node
   */
  protected function visitChildNodes(AbstractRenderNode $node)
  {
    $currentContent = $this->content;
    $children = $node->getChildren();
    foreach ($children as $childNode) {
      $childNode->accept($this); // calls visitDynamicHTMLNode |  visitLegacyNode -> createNodeContent-> set this->content
      $currentContent['children'][] = $this->content;
    }
    $this->content = $currentContent;
  }

  /**
   * @param AbstractRenderNode $node
   */
  protected function createNodeContent(AbstractRenderNode $node)
  {
    $this->content = $node->getUnit()->toArray();
  }

  /**
   * @param string $moduleId
   */
  protected function addModuleToUsedList($moduleId)
  {
    if (!isset($this->usedModuleIds[$moduleId])) {
      $this->usedModuleIds[$moduleId] = true;
    }
  }

  protected function activateLegacySupport()
  {
    $this->legacySupport = true;
  }
}
