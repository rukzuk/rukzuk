<?php


namespace Render\Visitors;

use Render\APIs\APIv1\APIv1Factory;
use Render\APIs\RootAPIv1\RootAPIv1Factory;
use Render\Exceptions\ModuleAPITypeNotFound;
use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\LegacyNode;
use Render\Nodes\AbstractRenderNode;
use Render\RenderContext;

abstract class AbstractVisitor
{

  private $renderContext;

  /**
   * @param \Render\RenderContext $renderContext
   */
  public function __construct(RenderContext $renderContext)
  {
    $this->renderContext = $renderContext;
  }

  /**
   * @param DynamicHTMLNode $node
   *
   * @throws ModuleAPITypeNotFound
   * @return APIv1Factory
   */
  protected function getApiFactory(DynamicHTMLNode $node)
  {
    $apiType = $node->getModuleApiType();
    if ($apiType === 'APIv1') {
      return new APIv1Factory($this->getRenderContext(), $node->getTree());
    } elseif ($apiType === 'RootAPIv1') {
      return new RootAPIv1Factory($this->getRenderContext(), $node->getTree());
    } else {
      throw new ModuleAPITypeNotFound();
    }
  }

  protected function visitChildNodes(AbstractRenderNode $node)
  {
    $children = $node->getChildren();
    foreach ($children as $childNode) {
      $childNode->accept($this);
    }
  }

  /**
   * @return \Render\RenderContext
   */
  public function getRenderContext()
  {
    return $this->renderContext;
  }

  abstract public function visitDynamicHTMLNode(DynamicHTMLNode $node);

  abstract public function visitLegacyNode(LegacyNode $node);
}
