<?php


namespace Render\APIs\RootAPIv1;

use Render\APIs\APIv1\RenderAPI;
use Render\Unit;
use Render\Visitors\CssOnlyVisitor;
use Render\Visitors\ModuleDataVisitor;

class RootRenderAPI extends RenderAPI
{

  /**
   * Returns the url for the js api
   *
   * @return string
   */
  public function getJsApiUrl()
  {
    if (!$this->isEditMode()) {
      return null;
    }
    return $this->getRenderContext()->getJsApiUrl();
  }

  /**
   * Output the css code
   *
   * @param Unit $rootUnit  unit where css traversing starts (root module)
   */
  public function insertCss(Unit $rootUnit)
  {
    $node = $this->getNodeByUnitId($rootUnit->getId());
    $node->accept(new CssOnlyVisitor($this->getRenderContext()));
  }

  /**
   * Returns a map list of all provided module data
   *
   * @param Unit $rootUnit
   *
   * @return array
   */
  public function getAllModuleData(Unit $rootUnit)
  {
    $node = $this->getNodeByUnitId($rootUnit->getId());
    $moduleInfoVisitor = new ModuleDataVisitor($this->getRenderContext());
    $node->accept($moduleInfoVisitor);
    return $moduleInfoVisitor->getModuleData();
  }

  /**
   * URL of the (complete) CSS rendered in an external file.
   * This file contains all rules from the current Page (or Template).
   * @return string
   */
  public function getCSSUrl()
  {
    return $this->getRenderContext()->getNavigationInfoStorage()->getCurrentCssUrl();
  }

  /**
   * Sets the locale information
   *
   * @param string $locale  The language code (examples: en_US; de_DE; de_CH)
   */
  public function setLocale($locale)
  {
    $this->getRenderContext()->setLocale($locale);
  }
}
