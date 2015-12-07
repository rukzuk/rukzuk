<?php
namespace Dual\Render;

/**
 * Webpage
 *
 * @package      Dual
 * @subpackage   Render
 */
class Webpage
{
  protected $data = array();
  protected $globalData = array();

  public function setArray($data)
  {
    $this->data = $data;
  }

  public function setGlobalArray($globalData)
  {
    $this->globalData = $globalData;
  }
  
  public function get($attribute)
  {
    if (isset($this->data) && is_array($this->data) && isset($this->data[$attribute])) {
    // Wert zurueckgeben
      return $this->data[$attribute];
    }
    
    // Nicht zum Zurueckgeben
    return;
  }

  /**
   * Globale Variable der Webpage zurueckgeben
   * @param   string  $varName    Name der globalen Variable welche zurueckgegeben werden soll
   * @param   string  $index      Bestimmten Wert der globalen Variable zurueckgeben
   * @access public
   */
  public function getGlobal($varName, $index = false)
  {
    if (isset($this->globalData) && is_array($this->globalData)
        && isset($this->globalData[$varName]) && is_array($this->globalData[$varName])) {
    // Alle Variablen zurueckgeben
      if ($index === false) {
        return $this->globalData[$varName];
      } // Speziellen Variablen zurueckgeben
      else {
        $index = (int)$index;
        if ($index >= 0) {
        // Stelle vom Anfang des Array
          if (isset($this->globalData[$varName][$index])) {
            return $this->globalData[$varName][$index];
          }
        } elseif ($index < 0) {
        // Stelle vom Ende des Array
          $index = count($this->globalData[$varName])-abs($index);
          if (isset($this->globalData[$varName][$index])) {
            return $this->globalData[$varName][$index];
          }
        }
      }
    }

    // Nichts zum Zurueckgeben
    return;
  }

  public function getNavigationTitle()
  {
    $navTitle = $this->get('navigationTitle');
    if (empty($navTitle)) {
      $navTitle =  $this->get('name');
    }
    return $navTitle;
  }
}
