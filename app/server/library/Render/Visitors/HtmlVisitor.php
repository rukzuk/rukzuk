<?php


namespace Render\Visitors;

use \Render\Nodes\DynamicHTMLNode;
use \Render\Nodes\LegacyNode;

class HtmlVisitor extends AbstractVisitor
{

  public function visitLegacyNode(LegacyNode $node)
  {
    $node->renderHtmlOutput($this, $this->getRenderContext());
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $node->renderHtmlOutput($this->getRenderAPI($node));
  }

  /**
   * @param DynamicHTMLNode $node
   *
   * @return \Render\APIs\APIv1\RenderAPI
   */
  protected function getRenderAPI(DynamicHTMLNode $node)
  {
    return $this->getApiFactory($node)->getRenderAPI($this);
  }
}
