<?php


namespace Render\Visitors;

use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\FullDynamicNode;
use Render\Nodes\LegacyNode;

class CssOnlyVisitor extends AbstractVisitor
{

  public function visitLegacyNode(LegacyNode $node)
  {
    $node->renderUnitCss();
    $this->visitChildNodes($node);
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $node->renderCssOutput($this->getCssAPI($node));
    $this->visitChildNodes($node);
  }

  /**
   * @param DynamicHTMLNode $node
   *
   * @return \Render\APIs\APIv1\RenderAPI
   */
  protected function getCssAPI(DynamicHTMLNode $node)
  {
    return $this->getApiFactory($node)->getRenderAPI($this);
  }
}
