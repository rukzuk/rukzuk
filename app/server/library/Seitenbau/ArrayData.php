<?php
namespace Seitenbau;

/**
 * Array Data
 *
 * Bietet Funktionen zum einfuegen und verschieben von Multi-Array-Daten
 *
 * @package    Seitenbau
 * @subpackage ArrayData
 */

class ArrayData extends \ArrayObject
{
  protected $childMarker = 'children';

  protected $referenceMarker = 'id';

  protected $handleEntry = null;

  protected $parentEntry = null;

  protected $parentRoot = 'root';

  public function __construct()
  {
  }

  /**
   * setzt den key, an dem Vergleiche durchgefuehrt werden
   *
   * @param string $referenceKey
   */
  public function setReferenceMarker($referenceMarker)
  {
    $this->referenceMarker = $referenceMarker;
  }

  /**
   * Gibt den Wert des ReferenceMarker zurueck
   *
   * @return string
   */
  public function getReferenceMarker()
  {
    return $this->referenceMarker;
  }

  /**
   * Seten den Marker, unter welchen Child-Elemente referenziert werden
   *
   * @param string $childMarker
   */
  public function setChildMarker($childMarker)
  {
    $this->childMarker = $childMarker;
  }

  /**
   * Gibt den Wert des ChildMarker zurueck
   *
   * @return string
   */
  public function getChildMarker()
  {
    return $this->childMarker;
  }

  /**
   * Fuegt einen neuen Eintrag in das Daten-Array
   *
   * @param array $data Daten Array
   * @param array $newEntry neues Element
   * @param string $parentValue Wert des Parents, unter dem eingefuegt wird
   * @param string $beforeId  Wert, vor dem das Element eingefuegt wird
   * @return array|false  Daten Array
   */
  public function insert($data, $newEntry, $parentValue, $beforeId = null)
  {
    $this->handleEntry = $newEntry;
    $this->searchEntriesForInsert($data, null, $parentValue);

    if ($this->isHandleEntryAndParentEntrySet()) {
      $this->makeChildMarker($this->parentEntry);
      $isAdded = $this->addToEntry($this->parentEntry, $beforeId);
    } elseif (!is_null($this->handleEntry) && $parentValue == $this->parentRoot) {
      $isAdded = $this->addToRoot($data, $beforeId);
    }

    if ($isAdded) {
      return $data;
    } else {
      return false;
    }
  }

  /**
   * Fuegt einen Eintrag in das Daten-Array nach einem uebergebenen Eintrag
   *
   * @param array $data Daten Array
   * @param array $newEntry neues Element
   * @param string $beforeId  Wert, nach dem das neue Element eingefuegt wird
   */
  public function insertAfter($data, $newEntry, $beforeId)
  {
    $this->insertAfterId($data, $newEntry, $beforeId);

    return $data;
  }

  /**
   * Verschiebt einen Eintrag im Array
   *
   * @param array $data Array mit Daten
   * @param string $refValue Wert zum ermitteln des Eintrag
   * @param string $parentValue Wert des Parent, an den der Eintrag eingefuegt wird
   * @param string  $beofreId Wert, vor dem das Element eingefuegt wird
   */
  public function move($data, $refValue, $parentValue, $beforeId = null)
  {
    $this->searchEntriesForInsert($data, $refValue, $parentValue);

    $isAdded = false;
    if ($this->isHandleEntryAndParentEntrySet()) {
      $this->makeChildMarker($this->parentEntry);
      $isAdded = $this->addToEntry($this->parentEntry, $beforeId);
    } elseif (!is_null($this->handleEntry) && $parentValue == $this->parentRoot) {
      $isAdded = $this->addToRoot($data, $beforeId);
    }

    if ($isAdded) {
      return $data;
    } else {
      return false;
    }
  }

  /**
   * Fuegt in eine uebergebene Datenstruktur weitere Daten hinzu
   * Es wird anhand des Key-Namen vom ReferenceMarker (Standard: id)
   *
   * Die uebergebenen Daten muessen folgende Struktur aufweisen:
   * $data = array(
   *  'wertDesReferenceMarker1' => array(
   *   'keyData1' => 'valueData1', 'keyData2' => 'valueData2'
   *  )
   * )
   *
   * @param array $struktur
   * @param array $data
   */
  public function mergeData(array &$struktur, array $data)
  {
    foreach ($struktur as &$node) {
      if (isset($node[$this->getReferenceMarker()])
          && array_key_exists($node[$this->getReferenceMarker()], $data)) {
        foreach ($data[$node[$this->getReferenceMarker()]] as $key => $value) {
          $node[$key] = $value;
        }
      }

      if (isset($node[$this->getChildMarker()])
          && is_array($node[$this->getChildMarker()])) {
        $this->mergeData($node[$this->getChildMarker()], $data);
      }
    }
  }

  /**
   * Entfernt den Eintrag, welcher als ReferenceMarker-Wert den RefValue hat
   *
   * @param array $data Daten-Array
   * @param string $refValue Wert, vom Reference-Key zum Erkennen des Eintrag
   */
  public function remove(&$data, $refValue)
  {
    $zaehler = 0;
    foreach ($data as &$entry) {
      if ($this->referenceMarkerHasValue($entry, $refValue)) {
        unset ($data[$zaehler]);
        $data = array_merge(array(), $data);
        return true;
      } elseif ($this->childMarkerHasValues($entry)) {
        $this->remove($entry[$this->getChildMarker()], $refValue);
      }
      $zaehler++;
    }
  }

  /**
   * Speichert alle Values des Reference-Marker-Keys als flaches Array ab
   *
   * @param array $values
   * @param array $data
   */
  public function setValuesAsArray(&$values, $data)
  {
    foreach ($data as $entry) {
      array_push($values, $entry[$this->getReferenceMarker()]);
      if ($this->childMarkerHasValues($entry)) {
        $this->setValuesAsArray($values, $entry[$this->getChildMarker()]);
      }
    }
  }

  /**
   * Sucht das Element sowie das Parent aus dem Datenbestand
   * optional kann das bestehende geloescht werden
   *
   * @param array $data Referenz auf Array-Datensatz, welcher bearbeitet wird
   * @param string $refValue Wert vom Reference-Key zum erkennen des Eintrag
   * @param string $parentValue  Wert, unter dem Einsortiert werden soll
   * @param boolean $delete wenn true, dann wird der verschobene Wert geloescht
   */
  protected function searchEntriesForInsert(&$data, $refValue, $parentValue, $delete = true)
  {
    $entryCounter = 0;
    
    if (count($data) == 0) {
      return $entryCounter;
    }

    foreach ($data as &$entry) {
    // Handle und Parent Entry gefunden -> abbrechen
      if ($this->isHandleEntryAndParentEntrySet()) {
        break;
      }
      // Handle Entry setzen (wenn Pruefung positiv)
      if ($this->referenceMarkerHasValue($entry, $refValue)) {
        $this->handleEntry =& $data[$entryCounter];
        if ($delete) {
          unset($data[$entryCounter]);
          $entryCounter--;
          $data = array_merge(array(), $data);
          $this->searchEntriesForInsert($data, $refValue, $parentValue);
        }
      }

      // Parent Entry setzen (wenn Pruefung positiv)
      if ($this->referenceMarkerHasValue($entry, $parentValue)) {
        $this->parentEntry =& $data[$entryCounter];
      }
      // Childrens rekursiv aufrufen
      if ($this->childMarkerHasValues($entry)) {
        $this->searchEntriesForInsert(
            $entry[$this->childMarker],
            $refValue,
            $parentValue
        );
      }
      $entryCounter++;
    }
  }

  /**
   * Sucht die Entries zum Insert After Funktionalitaet
   * Dafuer wird die Parent und evtl. InsertBefore Entry gesucht
   *
   * @param array $data Referenz auf Array-Datensatz, welcher bearbeitet wird
   * @param string $beforeId  ID des Eintrags, zu dem der Parent und der Before
   *  Entry gesucht wird
   */
  protected function insertAfterId(&$data, $newEntry, $beforeId)
  {
    $zaehler = 1;
    foreach ($data as $id => &$entry) {
      if ($this->referenceMarkerHasValue($entry, $beforeId)) {
        array_splice($data, $zaehler, 0, array($newEntry));
        break;
      } elseif ($this->childMarkerHasValues($entry)) {
        $this->insertAfterId($entry[$this->childMarker], $newEntry, $beforeId);
      }
      $zaehler++;
    }
  }

  /**
   * Fuegt den gefunden Eintrag in den referenzierten Eintrag ein
   *
   * @param array $entry  Referenz auf Eintrag, unter welchen der neue Eintrag eingefuegt wird
   * @param string $beforeId ID, vor welchen Untereintrag
   * @return  boolean true, wenn erfolgreich, sonst false
   */
  protected function addToEntry(&$entry, $beforeId = null)
  {
    if (is_null($beforeId) || $beforeId == '') {
      $entry[$this->childMarker][] = $this->handleEntry;
      return true;
    } else {
      $zaehler = 0;
      foreach ($entry[$this->childMarker] as $childEntry) {
        if ($childEntry['id'] == $beforeId) {
          array_splice($entry[$this->childMarker], $zaehler, 0, array($this->handleEntry));
          return true;
        }
        $zaehler++;
      }
    }
    return false;
  }

  protected function addToRoot(&$data, $beforeId = null)
  {
    if (is_null($beforeId) || $beforeId == '') {
      $data[] = $this->handleEntry;
      return true;
    } else {
      $zaehler = 0;
      foreach ($data as $entry) {
        if ($entry['id'] == $beforeId) {
          array_splice($data, $zaehler, 0, array($this->handleEntry));
          return true;
        }
        $zaehler++;
      }
    }
    return false;
  }

  /**
   * Erstellt einen Child-Key, wenn noch keiner vorhanden ist
   *
   * @param array $entry Referenz auf Entry
   */
  protected function makeChildMarker(&$entry)
  {
    if (!isset($entry[$this->childMarker])) {
      $entry[$this->childMarker] = array();
    }
  }

  /**
   * Prueft, ob der Entry bereits Child-Element enthaellt
   *
   * @param array $entry  Referenz auf Eintrag
   * @return boolean
   */
  protected function childMarkerHasValues($entry)
  {
    if (isset($entry[$this->childMarker])
        && is_array($entry[$this->childMarker])
        && count($entry[$this->childMarker]) > 0) {
      return true;
    }
    return false;
  }

  /**
   * Prueft, ob der Entry den Reference-Marker-Key enthaellt und dieser
   * den entsprechenden Wert besitzt
   *
   * @param array $entry
   * @param string $refValue
   * @return boolean
   */
  protected function referenceMarkerHasValue($entry, $refValue)
  {
    if (isset($entry[$this->referenceMarker])
        && $entry[$this->referenceMarker] == $refValue) {
      return true;
    }
    return false;
  }

  /**
   * Prueft, ob das Handle-Entry sowie das Parent-Entry gesetzt wurd
   *
   * @return boolean
   */
  protected function isHandleEntryAndParentEntrySet()
  {
    if ($this->handleEntry != null && $this->parentEntry != null) {
      return true;
    }
    return false;
  }
}
