<?php
namespace Dual\Render;

/**
 * Listen-Basis-Klasse
 *
 * @package     Dual
 * @subpackage  Render
 */
class BaseList implements \Countable, \Iterator, \ArrayAccess
{
  /**
   * @var     string     Maximale Anzahl der Eintreage
   * @access  protected
   */
  protected $iMaxObjects = 10000;

  /**
   * @var     array      Liste mit den vorhandenen Keys
   * @access  protected
   */
  protected $aKeys = array();

  /**
   * @var     hash       Liste mit den vorhandenen Objecten
   * @access  protected
   */
  protected $hObjects = array();

  /**
   * @var     hash       Liste mit den vorhandenen Objecten
   * @access  protected
   */
  protected $hData = array();

  /**
   * @var     integer     Aktuelle Position in der Liste
   * @access  protected
   */
  protected $pos = 0;

  /**
   * @var     integer     Anzahl gefundener DB-Eintraege
   * @access  protected
   */
  protected $iDbCount = 0;

  /**
   * Konstruktor.
   */
  public function __construct()
  {

  }

  /**
   * Liefert die maximale Anzahl aufzunehmender Objecte in dieser Liste zurueck
   */
  public function getMaxObjects()
  {
    return $this->iMaxObjects;
  }

  /**
   * Setzt die maximale Anzahl aufzunehmender Objecte in dieser Liste
   */
  public function setMaxObjects($iMaxObjects)
  {
    $this->iMaxObjects = $iMaxObjects;
  }

  /**
   * Array mit Daten hinzufuegen
   * @param   array   $ahData  Array mit den Objekt-Daten
   * @access public
   */
  public function add($sUid, &$oObject)
  {
    if (!isset($this->hObjects[$sUid])) {
    // UID als Key aufnehmen
      $this->aKeys[] = $sUid;
    }

    // Objekt aufnehmen
    $this->hObjects[$sUid] = $oObject;
  }

  /**
   * Gibt ein Object zurueck
   * @param  hash    $sKey    Key des Objectes
   * @access public
   */
  public function get($key)
  {
    return $this->hObjects[$key];
  }


  /** ********************************************************************** **
   *  Interface: Countable
   ** ********************************************************************** **/

  /**
   * Countable-Funktion
   *
   * Anzahl Objecte zurueckgeben
   * @access public
   */
  public function count()
  {
    // Anzahl vorhandener Objecte zurueckgeben
    return count($this->aKeys);
  }


  /** ********************************************************************** **
   *  Interface: Iterator
   ** ********************************************************************** **/

  /**
   * Iterator-Funktion
   *
   * Zuruecksetzen auf die erste Zeile
   */
  public function rewind()
  {
    $this->pos = 0;
  }

  /**
   * Iterator-Funktion
   *
   * Gibt den Key der aktuellen Zeile zurueck
   */
  public function key()
  {
    return $this->aKeys[$this->pos];
  }

  /**
   * Iterator-Funktion
   *
   * Zeiger auf die naechste Zeile setzen
   */
  public function next()
  {
    $this->pos++;
  }

  /**
   * Iterator-Funktion
   *
   * Sind wir auf einer Gueltigen Zeile
   */
  public function valid()
  {
    if ($this->pos < count($this->aKeys)) {
    // Gueltig
      return true;
    }

    // Nicht Gueltig
    return false;
  }

  /**
   * Iterator-Funktion
   *
   * Zurueckgeben des Track-Objekt der aktuellen Zeile
   */
  public function current()
  {
    return $this->get($this->key());
  }


  /** ********************************************************************** **
   *  Interface: ArrayAccess
   ** ********************************************************************** **/

  /**
   * ArrayAccess-Funktion
   *
   * Ist ein bestimmtes Object vorhanden
   */
  public function offsetExists($offset)
  {
    // Offest oder Key?
    if (is_integer($offset)) {
    // Key ermitteln
      $offset = $this->aKeys[$offset];
    }

    // Auf Daten oder Object ueberpruefen
    if (isset($this->hData[$offset])
        || (isset($this->hObjects[$offset]) && is_object($this->hObjects[$offset]))) {
    // Object vrohanden
      return true;
    }

    // Nicht vorhanden
    return false;
  }

  /**
   * ArrayAccess-Funktion
   *
   * Liefert ein bestimmtes Object zurueck
   */
  public function offsetGet($offset)
  {
    // Offest oder Key?
    if (is_integer($offset)) {
    // Key ermitteln
      $offset = $this->aKeys[$offset];
    }

    // Object zurueckgeben
    return $this->get($offset);
  }

  /**
   * ArrayAccess-Funktion
   *
   * Setzt ein bestimmtes Object
   */
  public function offsetSet($offset, $value)
  {
    // Offest oder Key?
    if (is_integer($offset)) {
    // Key ermitteln
      $offset = $this->aKeys[$offset];
    }

    // Wird ein Object uebergeben
    if (is_object($value)) {
    // Object setzen
      $this->hObjects[$offset] = $value;

      // Erfolgreich gesetzt
      return true;
    }

    // Fehler
    return false;
  }

  /**
   * ArrayAccess-Funktion
   *
   * loescht ein bestimmtes Object
   */
  public function offsetUnset($offset)
  {
    // Offest oder Key?
    if (is_integer($offset)) {
    // Key ermitteln
      $offset = $this->aKeys[$offset];
    }

    // Object und Array-Daten loeschen
    unset( $this->hObjects[$offset] );
    unset( $this->hData[$offset] );

    // Key aus der Key-Liste entfernen
    for ($iCount=0; $iCount < count($this->aKeys); $iCount++) {
    // Key Gefunden?
      if ($this->aKeys[$iCount] == $offset) {
      // Loeschen und beenden
        unset( $this->aKeys[$iCount] );
        break;
      }
    }
  }
}
