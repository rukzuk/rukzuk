<?php


namespace Render;

use Render\Nodes\INode;
use Render\NodeTree;
use Render\Visitors\CssOnlyVisitor;
use Render\Visitors\HtmlVisitor;

abstract class AbstractRenderer
{
  /**
   * @return NodeTree
   */
  abstract protected function getNodeTree();

  /**
   * @return RenderContext
   */
  abstract protected function getRenderContext();


  /**
   * Render html
   */
  public function renderHtml()
  {
    if ($this->isSessionRequired()) {
      $this->startSession();
    }
    $visitor = $this->getHtmlVisitor();
    $this->getRootNode()->accept($visitor);
  }

  /**
   * Render css
   */
  public function renderCss()
  {
    $visitor = $this->getCssOnlyVisitor();
    $this->getRootNode()->accept($visitor);
  }

  /**
   * @return HtmlVisitor
   */
  protected function getHtmlVisitor()
  {
    return new HtmlVisitor($this->getRenderContext());
  }

  /**
   * @return CssOnlyVisitor
   */
  protected function getCssOnlyVisitor()
  {
    return new CssOnlyVisitor($this->getRenderContext());
  }

  /**
   * @return INode
   */
  protected function getRootNode()
  {
    return $this->getNodeTree()->getRootNode();
  }

  /**
   * starts the session
   */
  protected function startSession()
  {
    if (headers_sent()) {
      return;
    }

    if ((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE)
      || !session_id()
    ) {
      session_start();
    }
  }

  /**
   * @return boolean
   */
  protected function isSessionRequired()
  {
    $usedModuleIds = $this->getNodeTree()->getUsedModuleIds();
    $moduleInfoStorage = $this->getModuleInfoStorage();
    foreach ($usedModuleIds as $moduleId) {
      $manifest = $moduleInfoStorage->getModuleManifest($moduleId);
      if (isset($manifest['sessionRequired']) && $manifest['sessionRequired'] == true) {
        return true;
      }
    }
    return false;
  }

  /**
   * @return InfoStorage\ModuleInfoStorage\IModuleInfoStorage
   */
  protected function getModuleInfoStorage()
  {
    return $this->getRenderContext()->getModuleInfoStorage();
  }
}
