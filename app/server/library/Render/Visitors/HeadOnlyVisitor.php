<?php

namespace Render\Visitors;

use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\FullDynamicNode;
use Render\Nodes\LegacyNode;

class HeadOnlyVisitor extends AbstractVisitor
{

  private $renderedModules = array();

  protected function isNotRendered($moduleId)
  {
    return !isset($this->renderedModules[$moduleId]);
  }

  protected function markAsRendered($moduleId)
  {
    $this->renderedModules[$moduleId] = true;
  }

  public function visitLegacyNode(LegacyNode $node)
  {
    $moduleId = $node->getModuleId();
    if ($this->isNotRendered($moduleId)) {
      $node->renderModuleHead();
      $this->markAsRendered($moduleId);
    }
    $this->visitChildNodes($node);
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $moduleId = $node->getModuleId();
    if ($this->isNotRendered($moduleId)) {
      $api = $this->getDynamicHTMLNodeAPI($node);
      $data = $node->provideModuleData($api);
      if (isset($data['header'])) {
        echo $data['header'];
      }
      $this->markAsRendered($moduleId);
    }
    $this->visitChildNodes($node);
  }

  /**
   * @param DynamicHTMLNode $node
   *
   * @return \Render\APIs\APIv1\RenderAPI
   */
  protected function getDynamicHTMLNodeAPI(DynamicHTMLNode $node)
  {
    return $this->getApiFactory($node)->getHeadAPI();
  }
}
