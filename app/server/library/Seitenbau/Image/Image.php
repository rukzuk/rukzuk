<?php
namespace Seitenbau\Image;

/**
 * Bildbearbeitungs Vorlage
 *
 * @package      Seitenbau
 * @subpackage   Image
 */

interface Image
{
  const TYPE_GIF = 'gif';
  const TYPE_JPG = 'jpg';
  const TYPE_PNG = 'png';
  const TYPE_WBMP = 'wbmp';

  /**
   * @param string $filePath
   *
   * @return array
   */
  public function getDimensionFromFile($filePath);

  /**
   * Hier ist das Setzen der orginal Bilddatei implementiert
   *
   * @param string  $imageFile  Bilddatei, welche veraendert werden soll
   */
  public function setFile($imageFile);

  /**
   * Hier soll im Adapter das (neu) Oeffnen des Bildes implementiert werden
   */
  public function load();

  /**
   * Hier soll im Adapter das Speichern des Bildes implementiert werden
   *
   * @param string  $destinationFile  Datei, in welcher das neue Bild gespeichert wird
   * @param string  $imageType      Bildformat welches erzeugt werden soll
   */
  public function save($destinationFile, $imageType = null);

  /**
   * Hier soll im Adapter das direkte Ausgeben des Bildes implementiert werden
   *
   * @param string  $imageType      Bildformat welches erzeugt werden soll
   */
  public function send($imageType = null);

  /**
   * Hier soll im Adapter das Schliessen des Bildes implementiert werden
   */
  public function close();

  /**
   * Hier soll im Adapter das Ermitteln der Bildgroesse implementiert werden
   */
  public function getImageSize();

  /**
   * Aktuelle Bildgroesse zurueckgeben
   */
  public function getCurImageSize();

  /**
   * Hier soll im Adapter das Ermitteln der Bildinformationen implementiert werden
   */
  public function getImageInfo();

  /**
   * Hier soll im Adapter das Aendern der Bildgroesse implementiert werden
   *
   * @param array  $action
   */
  public function resize($action);

  /**
   * Hier soll im Adapter das Ausschneiden eines Bildbereiches implementiert werden
   *
   * @param array  $action
   */
  public function crop($action);

  /**
   * Hier ist das Setzen der Qualitaet ueber eine Aktion implementiert
   *
   * @param array  $action
   */
  public function quality($action);

  /**
   * Hier ist das Setzen der Qualitaet implementiert
   *
   * @param integer  $quality  Qualitaet des Bildes
   */
  public function setQuality($quality);

  /**
   * Returns TRUE if the file specified by $filePath can be handled as image; FALSE otherwise.
   *
   * @param $filePath
   *
   * @return boolean
   */
  public function isImageFile($filePath);
}
