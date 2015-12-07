<?php
namespace Dual\Render;

/**
 * Website
 *
 * @package      Dual
 * @subpackage   Render
 */

class Website
{
  protected $data = array();
  protected $colors = null;
  protected $resolutions = null;

  public function setArray($data)
  {
    $this->data = $data;
  }

  private function createColorValues()
  {
    $this->colors = array( );
    if (isset($this->data['colorscheme'])) {
    // Bereits als Array vorhanden
      if (is_array($this->data['colorscheme'])) {
        $this->colors = $this->data['colorscheme'];
      } elseif (is_string($this->data['colorscheme'])) {
        try {
          // JSON decodieren und Array aufbereiten
          $colorscheme = json_decode($this->data['colorscheme'], true);
          if (is_array($colorscheme)) {
            foreach ($colorscheme as $colorId => $color) {
              $this->colors[$color['id']] = $color;
            }
          }
        } catch (\Exception $e) {
        // No Error Handling
        }
      }
    }
  }
  
  private function createResolutionValues()
  {
    $this->resolutions = array();
    if (isset($this->data['resolutions'])) {
    // Bereits als Array vorhanden
      if (is_array($this->data['resolutions'])) {
        $this->resolutions = $this->data['resolutions'];
      } elseif (is_string($this->data['resolutions'])) {
        try {
          // JSON decodieren
          $this->resolutions = json_decode($this->data['resolutions'], true);
        } catch (\Exception $e) {
        // No Error Handling
        }
      }
    }
  }

  public function get($attribute)
  {
    if (isset($this->data) && is_array($this->data)
        && isset($this->data[$attribute]) ) {
      switch($attribute) {
        case 'resolutions':
          if (!isset($this->resolutions) && !is_array($this->resolutions)) {
            $this->createResolutionValues();
          }
              return $this->resolutions;
          break;
        
        default:
            // Wert zurueckgeben
              return $this->data[$attribute];
          break;
      }
    }
    
    // Nicht zum Zurueckgeben
    return;
  }

  /**
   * Farbe aus dem Farbschema der Website zurueckgeben
   * @param   string  $colorId    Id der gewuenschten Farbe
   * @param   string  $type       Typ der gewuenschten Farbe
   * @access public
   */
  public function getColorById($colorId, $type = WebsiteColor::COLOR_RGBA)
  {
    $curColor = null;
    $orgColorId = $colorId;
  
    // evtl. Farb-Array erzeugen
    if (!isset($this->colors) && !is_array($this->colors)) {
      $this->createColorValues();
    }
    
    if (isset($this->colors) && is_array($this->colors)) {
      if (!isset($this->colors[$colorId])) {
        // get base colorId
        $colorId = WebsiteColor::getBaseIdFromColorId($colorId);
      }
      if (isset($this->colors[$colorId])) {
        // return color
        if (empty($type)) {
          return $this->colors[$colorId]['value'];
        }
        if (!isset($this->colors[$colorId]['type'][$type])) {
          $this->colors[$colorId]['type'][$type] = WebsiteColor::convertColorTo($this->colors[$colorId]['value'], $type);
        }
        return $this->colors[$colorId]['type'][$type];
      }
    }
    
    // get fallback color form colorId
    $fallbackColor = WebsiteColor::getFallbackColorFormColorId($orgColorId);
    if (!empty($fallbackColor)) {
      return WebsiteColor::convertColorTo($fallbackColor, $type);
    }

    // Nichts zum Zurueckgeben
    return $orgColorId;
  }
}
