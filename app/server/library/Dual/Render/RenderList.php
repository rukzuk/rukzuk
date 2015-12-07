<?php
namespace Dual\Render;

use Dual\Render\RenderObject as RenderObject;
use Dual\Render\RenderContext as RenderContext;

/**
 * @package      Dual
 * @subpackage   Render
 */

class RenderList extends BaseList
{
  private $parentUnit     = null;

  /**
   * Konstruktor.
   */
  public function __construct()
  {
    // ******************************************
    // Parent-Klasse aufrufen
    parent::__construct();
    // ******************************************
  }

  public function setParent(&$parentUnit)
  {
    $this->parentUnit = $parentUnit;
  }

  public function getParent()
  {
    return $this->parentUnit;
  }

  protected function getUnitClassName(&$unitData)
  {
    // Unit-Klassennamen ermitteln
    $unitClassName = null;
    if (\is_array($unitData) || \is_object($unitData)) {
    // "OnTheFly"-Seite direkt die Module erzeugen und mit Daten fuellen
      $unitClassName .= __NAMESPACE__.'\\RenderObject';
    } elseif (\is_string($unitData) && !empty($unitData)) {
    // Name der der UnitKlasse aufnehmen
      $unitClassName .= __NAMESPACE__.'\\'.$unitData;
    }

    // Klassennamen zurueckgeben
    return $unitClassName;
  }

  protected function &createUnit(&$unitData)
  {
    // init
    $unit = null;

    // Unit-Klassennamen ermitteln
    $unitClassName = $this->getUnitClassName($unitData);
    // Unit-Objekt erzeugen
    if (isset($unitClassName) && !empty($unitClassName)) {
    // Unit erzeugen
      $unit = new $unitClassName();
      $unit->setParent($this->parentUnit);
      $unit->setWebsiteId(RenderContext::getWebsiteId());
      $unit->setArray($unitData);
    }

    // Unit zurueckgeben
    return $unit;
  }

  public function setTree($tree)
  {
    if (\is_array($tree)) {
    // Alle Kind-Units erstellen
      foreach ($tree as $nextUnitData) {
      // Unit erstellen
        $unit = $this->createUnit($nextUnitData);

        if ($unit instanceof RenderObject) {
          $this->add($unit->getId(), $unit);
        }
      }
    }
  }

  public function renderHtml($config = null)
  {
    // init
    $result   = null;
    $exclude  = array();
    $buffer   = false;
    
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
