<?php


namespace Render\Visitors;

use Render\Nodes\AbstractRenderNode;
use Render\Nodes\DynamicHTMLNode;
use Render\Nodes\LegacyNode;

class ModuleDataVisitor extends AbstractVisitor
{

  private $moduleData = array();

  /**
   * @return array
   */
  public function getModuleData()
  {
    return $this->moduleData;
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $moduleId = $node->getModuleId();
    if (!$this->isVisited($moduleId)) {
      $api = $this->getApiFactory($node)->getHeadAPI();
      $this->moduleData[$moduleId] = $node->provideModuleData($api);
    }
    $this->visitChildNodes($node);
  }

  public function visitLegacyNode(LegacyNode $node)
  {
    $moduleId = $node->getModuleId();
    if (!$this->isVisited($moduleId)) {
      $head_output = $this->getLegacyModuleHeaderOutput($node);
      $this->moduleData[$moduleId] = array('header' => $head_output);
    }
    $this->visitChildNodes($node);
  }

  /**
   * @param LegacyNode $node
   *
   * @return string
   */
  protected function getLegacyModuleHeaderOutput(LegacyNode $node)
  {
    ob_start();
    $node->renderModuleHead();
    return ob_get_clean();
  }

  protected function isVisited($moduleId)
  {
    return isset($this->moduleData[$moduleId]);
  }
}
