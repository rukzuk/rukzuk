<?php


namespace Render\Visitors;

use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\LegacyNode;

class UnitDataVisitor extends AbstractVisitor
{

  private $unitData = array();

  /**
   * @return array
   */
  public function getUnitData()
  {
    return $this->unitData;
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $api = $this->getApiFactory($node)->getCSSAPI();
    $this->unitData[$node->getUnitId()] = $node->provideUnitData($api);
    $this->visitChildNodes($node);
  }

  public function visitLegacyNode(LegacyNode $node)
  {
    $this->unitData[$node->getUnitId()]  = null;
    $this->visitChildNodes($node);
  }
}
