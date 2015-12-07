<?php


namespace Render\APIs\APIv1;

use Render\APIs\IDynamicHTMLNodeAPIFactory;
use Render\Nodes\AbstractRenderNode;
use Render\NodeTree;
use Render\RenderContext;
use Render\Visitors\AbstractVisitor;

class APIv1Factory
{

  /**
   * @var RenderContext
   */
  private $renderContext;

  /**
   * @var NodeTree
   */
  private $nodeTree = array();

  /**
   * @param RenderContext        $renderContext
   * @param NodeTree $nodeTree
   */
  public function __construct(RenderContext $renderContext, $nodeTree)
  {
    $this->renderContext = $renderContext;
    $this->nodeTree = $nodeTree;
  }

  /**
   * @param AbstractVisitor $renderingVisitor
   *
   * @return RenderAPI
   */
  public function getRenderAPI(AbstractVisitor $renderingVisitor)
  {
    return new RenderAPI($renderingVisitor, $this->getNodeTree(), $this->getRenderContext());
  }

  /**
   * @return CSSAPI
   */
  public function getCSSAPI()
  {
    return new CSSAPI($this->getNodeTree(), $this->getRenderContext());
  }

  /**
   * @return HeadAPI
   */
  public function getHeadAPI()
  {
    return new HeadAPI($this->getRenderContext());
  }

  /**
   * @return RenderContext
   */
  protected function getRenderContext()
  {
    return $this->renderContext;
  }

  /**
   * @return NodeTree
   */
  protected function getNodeTree()
  {
    return $this->nodeTree;
  }
}
