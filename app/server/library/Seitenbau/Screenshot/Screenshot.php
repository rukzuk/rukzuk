<?php
namespace Seitenbau\Screenshot;

/**
 * Screenshot Vorlage
 *
 * @package      Seitenbau
 * @subpackage   Screenshot
 */

interface Screenshot
{
  /**
   * Erstellt einen Screenshot
   *
   * @param string  $shootId ID des Screenshots
   * @param \Cms\Business\Screenshot\Url $screenshotUrl   Quelle, von der der Screenshot erstellt werden soll
   * @param string  $destinationFile  Datei, in welcher der Screenshot gespeichert wird
   */
  public function shoot($shootId, $screenshotUrl, $destinationFile);

  /**
   * Prueft, ob das Screenshottool verwendbar ist
   */
  public function isUsable();
}
