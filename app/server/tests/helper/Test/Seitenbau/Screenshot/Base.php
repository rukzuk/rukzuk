<?php
namespace Test\Seitenbau\Screenshot;

use \Seitenbau\Screenshot\Base as SBScreenshotBase;

/**
 * Helfer Klasse zum Testen der Grundfunktionen von Screenshots
 * 
 * Da die Screenshot-Basis Klasse Abstrakt ist und somit nicht getestet werden
 * kann wird hier mit Hilfe dieser Klasse die Grundfunktionen der Basis getestet
 *
 * @package      Seitenbau
 */

class Base extends SBScreenshotBase
{
  public $countShoots = 0;

  public $countCreatedDirectories = 0;

  /**
   * Hier wird in den Typen die Erstellung des Screenshots implementiert
   *
   * @param string $shootId
   * @param \Cms\Business\Screenshot\Url $screenshotUrl
   * @param string  $destinationFile
   */
  protected function shootImplementation($shootId, $screenshotUrl, $destinationFile)
  {
    $this->countShoots++;
  }

  /**
   * Setzt die Optionen, welche nur fuer die entsprechende Einbindung 
   * gueltig sind
   */
  protected function setOptions()
  {
    
  }
  
  public function setActiv($activ)
  {
    parent::setActiv($activ);
  }
  
  public function setFiletype($filetype)
  {
    parent::setFiletype($filetype);
  }
  
  protected function createDirectory()
  {
    $this->countCreatedDirectories++;
    parent::createDirectory();
  }
}