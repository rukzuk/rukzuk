<?php


namespace Cms\Render;

use Render\AbstractRenderer;
use Render\Nodes\INode;
use Render\NodeTree;
use Render\RenderContext;

class CmsRenderer extends AbstractRenderer
{
  /**
   * @var RenderContext
   */
  private $renderContext;
  /**
   * @var NodeTree
   */
  private $nodeTree;

  /**
   * @param RenderContext $renderContext
   * @param NodeTree      $nodeTree
   */
  public function __construct(RenderContext $renderContext, NodeTree $nodeTree)
  {
    $this->renderContext = $renderContext;
    $this->nodeTree = $nodeTree;
  }

  /**
   * @return NodeTree
   */
  protected function getNodeTree()
  {
    return $this->nodeTree;
  }

  /**
   * @return RenderContext
   */
  protected function getRenderContext()
  {
    return $this->renderContext;
  }
}
