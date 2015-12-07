<?php


namespace Render\APIs;

use Render\Nodes\AbstractRenderNode;
use Render\Nodes\LegacyNode;
use Render\Visitors\HeadOnlyVisitor;
use Render\Visitors\CssOnlyVisitor;
use \Dual\Render\RenderObject;
use \Dual\Render\BaseList;
use \Dual\Render\CMS;
use Dual\Render\RenderContext;

class LegacyRenderList extends BaseList
{

  public function renderHtml($config = null)
  {
    // init
    $result = null;
    $exclude = array();
    $buffer = false;

    // Config uebergeben?
    if (is_array($config)) {
      // Buffer einschalten
      if (isset($config['buffer']) && $config['buffer'] === true) {
        $buffer = true;
        $result = array();
      }

      // Auszuschliessende Units ermitteln
      if (isset($config['include'])) {
        if (!is_array($config['include'])) {
          $config['include'] = array($config['include']);
        }
        if (!in_array(CMS::MODULE_TYPE_ALL, $config['include'])) {
          $exclude = array_diff(CMS::getModuleTypes(), $config['include']);
        }
      }
      if (isset($config['exclude'])) {
        if (!is_array($config['exclude'])) {
          $config['exclude'] = array($config['exclude']);
        }
        if (in_array(CMS::MODULE_TYPE_ALL, $config['exclude'])) {
          // Nichts rendern (Es wurden ALLE ausgeschlossen)
          return;
        }
        $exclude = array_merge($exclude, $config['exclude']);
      }
    }

    foreach ($this as $nextUnit) {
      // Auf den Modul-Typ aufpassen
      if (count($exclude) > 0) {
        if (in_array($nextUnit->getModuleType(), $exclude)) {
          // Unit ueberspringen
          continue;
        }
      }

      // Buffer einschalten
      if ($buffer) {
        ob_start();
      }

      // Unit rendern
      $nextUnit->renderHtml();

      if ($buffer) {
        $result[] = ob_get_clean();
      }
    }

    return $result;
  }

  public function renderCss(&$css)
  {
    foreach ($this as $nextUnit) {
      $nextUnit->renderCss($css);
    }
  }

  public function renderHead()
  {
    foreach ($this as $nextUnit) {
      $nextUnit->renderHead();
    }
  }
}

class LegacyModuleAPI extends RenderObject
{

  /**
   * @var \Render\Nodes\AbstractRenderNode
   */
  private $unitNode;
  private $defaultVisitor;
  private $newRenderContext;

  static private $moduleBaseValuesCache = array();
  
  public function __construct($defaultVisitor, AbstractRenderNode $unitNode, $newRenderContext)
  {
    $this->defaultVisitor = $defaultVisitor;
    $this->unitNode = $unitNode;
    $this->newRenderContext = $newRenderContext;

    $this->setWebsiteId(RenderContext::getWebsiteId());
    $this->setArray($unitNode->getUnit()->toArray());
    if ($unitNode instanceof LegacyNode) {
      $this->moduleAttributes = $unitNode->getModuleManifest();
    }
  }

  public function setDefaultVisitor($defaultVisitor)
  {
    if (!is_null($defaultVisitor)) {
      $this->defaultVisitor = $defaultVisitor;
    }
  }

  public function insertHead()
  {
    $this->unitNode->accept(new HeadOnlyVisitor($this->newRenderContext));
  }

  public function insertCss()
  {
    $this->unitNode->accept(new CssOnlyVisitor($this->newRenderContext));
  }

  public function renderHTML()
  {
    $this->unitNode->accept($this->defaultVisitor);
  }

  public function &createChildren()
  {
    $childNodes = $this->unitNode->getChildren();
    $childAPIs = new LegacyRenderList();

    foreach ($childNodes as $childNode) {
      $unitId = $childNode->getUnitId();
      if ($childNode instanceof LegacyNode) {
        $childAPI = $childNode->getModuleAPI($this->defaultVisitor, $this->newRenderContext);
      } else {
        // This is a dirty hack !!!
        // It allows the old root module the rendering of new modules.
        $childAPI = new LegacyModuleAPI($this->defaultVisitor, $childNode, $this->newRenderContext);
      }
      $childAPIs->add($unitId, $childAPI);
    }

    return $childAPIs;
  }

  public function getParent()
  {
    $parentNode = $this->unitNode->getParent();
    if (!is_object($parentNode)) {
      return null;
    }
    return $parentNode->getModuleAPI($this->defaultVisitor, $this->newRenderContext);
  }
  
  protected function getBaseValues()
  {
    if (RenderContext::getRenderType() == RenderContext::RENDER_STATIC) {
      return;
    }
    
    if ($this->initBaseValuesFromCache()) {
      return;
    }
    
    parent::getBaseValues();
    
    $this->cacheBaseValues();
  }
  
  private function initBaseValuesFromCache()
  {
    $moduleId = $this->getModuleId();
    if (!isset(self::$moduleBaseValuesCache[$moduleId])) {
      return false;
    }
    
    $this->RendererCode = self::$moduleBaseValuesCache[$moduleId]['rendererCode'];
    $this->CssCode = self::$moduleBaseValuesCache[$moduleId]['cssCode'];
    $this->HeadCode = self::$moduleBaseValuesCache[$moduleId]['headCode'];
    $this->moduleFormValues = self::$moduleBaseValuesCache[$moduleId]['moduleFormValues'];
    $this->moduleAttributes = self::$moduleBaseValuesCache[$moduleId]['moduleAttributes'];
    return true;
  }
  
  private function cacheBaseValues()
  {
    self::$moduleBaseValuesCache[$this->getModuleId()] = array(
      'rendererCode' => $this->RendererCode,
      'cssCode' => $this->CssCode,
      'headCode' => $this->HeadCode,
      'moduleFormValues' => $this->moduleFormValues,
      'moduleAttributes' => $this->moduleAttributes,
    );
  }
}
