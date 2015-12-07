<?php
namespace Seitenbau;

use Seitenbau\ArrayData;

/**
 * Komponententest für Seitenbau\ArrayData
 *
 * @package      Seitenbau
 * @subpackage   ArrayData
 */

class ArrayDataTest extends \PHPUnit_Framework_TestCase
{
  protected $arrayHandler = null;

  protected $testDatensatz = null;


  protected function setUp()
  {
    parent::setUp();

    $this->arrayHandler = new ArrayData();

    $this->generateTestData();
  }

  /**
   * @test
   * @group library
   */
  public function insertToParentEntrySuccess()
  {
    $data = array(
      array($this->arrayHandler->getReferenceMarker() => '100'),
      array($this->arrayHandler->getReferenceMarker() => '200'),
      array($this->arrayHandler->getReferenceMarker() => '300'),
    );
    
    $newEntry = array($this->arrayHandler->getReferenceMarker() => '101');
    
    $parentValue = '100';
    $newData = $this->arrayHandler->insert($data, $newEntry, $parentValue);

    $this->assertInternalType('array', $newData);
    foreach ($newData as $entry)
    {
      $this->assertInternalType('array', $entry);
      if ($entry[$this->arrayHandler->getReferenceMarker()] == $parentValue)
      {
        // Child Element pruefen
        $this->assertArrayHasKey($this->arrayHandler->getChildMarker(), $entry);
        $this->assertInternalType('array', $entry[$this->arrayHandler->getChildMarker()]);
        $this->assertSame($newEntry, $entry[$this->arrayHandler->getChildMarker()][0]);
      }
    }
  }

  /**
   * @test
   * @group library
   */
  public function insertToParentEntryBeforeEntrySuccess()
  {
    $newEntry = array($this->arrayHandler->getReferenceMarker() => '001');

    $parentValue = '320';
    
    $beforeId = '322';

    $newData = $this->arrayHandler->insert($this->testDatensatz, $newEntry,
      $parentValue, $beforeId);

    $this->checkEntryInsertBeforeId($newData, $newEntry, $parentValue,
      $beforeId);
  }

  /**
   * @test
   * @group library
   */
  public function moveToParentSuccess()
  {
    $refValue = '300';
    $parentValue = '100';

    $moveEntry = $this->testDatensatz[2];

    $newData = $this->arrayHandler->move($this->testDatensatz, $refValue,
      $parentValue);

    $this->assertInternalType('array', $newData);
    foreach ($newData as $entry)
    {
      $this->assertInternalType('array', $entry);
      if ($entry[$this->arrayHandler->getReferenceMarker()] == $parentValue)
      {
        // Child Element pruefen
        $this->assertArrayHasKey($this->arrayHandler->getChildMarker(), $entry);
        $this->assertInternalType('array', $entry[$this->arrayHandler->getChildMarker()]);

        $this->assertSame($moveEntry, $entry[$this->arrayHandler->getChildMarker()][0]);
      }
    }
  }

  /**
   * @test
   * @group library
   */
  public function moveToParentInsertBeforeSuccess()
  {
    $refValue = '300';
    $parentValue = '200';
    $insertBefore = '220';

    $moveEntry = $this->testDatensatz[2];

    $newData = $this->arrayHandler->move($this->testDatensatz, $refValue,
      $parentValue, $insertBefore);

    $this->checkEntryInsertBeforeId($newData, $moveEntry, $parentValue,
      $insertBefore);
  }

  /**
   * @test
   * @group library
   */
  public function moveToParentInsertBeforeMovableItemBeforeInsertItemSuccess()
  {
    $refValue = '100';
    $parentValue = '200';
    $insertBefore = '220';

    $moveEntry = $this->testDatensatz[0];
    
    $newData = $this->arrayHandler->move($this->testDatensatz, $refValue,
      $parentValue, $insertBefore);

    $this->checkEntryInsertBeforeId($newData, $moveEntry, $parentValue,
      $insertBefore);
  }

  /**
   * @test
   * @group library
   */
  public function moveToParentInsertBeforeMoveItemHasChildsAndListBeforeInsertItemSuccess()
  {
    $refValue = '200';
    $parentValue = '300';
    $insertBefore = '320';

    $moveEntry = $this->testDatensatz[1];

    $newData = $this->arrayHandler->move($this->testDatensatz, $refValue,
      $parentValue, $insertBefore);

    $this->checkEntryInsertBeforeId($newData, $moveEntry, $parentValue,
      $insertBefore);
  }

  /**
   * @test
   * @group library
   */
  public function mergeDataSuccess()
  {
    $data = array();
    $this->generateInfoData($data, $this->testDatensatz);

    $this->arrayHandler->mergeData($this->testDatensatz, $data);

    $this->checkMergeData($data, $this->testDatensatz);
  }

  /**
   * @test
   * @group library
   */
  public function removeSuccess()
  {
    $refValue = '320';
    $this->arrayHandler->remove($this->testDatensatz, $refValue);

    $this->checkRemoveData($this->testDatensatz, $refValue);
  }

  /**
   * @test
   * @group library
   */
  public function setValuesAsArraySuccess()
  {
    $values = array();
    $this->arrayHandler->setValuesAsArray($values, $this->testDatensatz);

    $this->checkSetValuesAsArray($this->testDatensatz, $values);
  }

  /**
   * Pruefung, ob Values ungeschachtelt zurueckgegeben wurden
   *
   * @param array $data
   * @param array $values
   */
  protected function checkSetValuesAsArray($data, $values)
  {
    foreach ($data as $entry)
    {
      $this->assertContains($entry['id'], $values);
      if (isset($entry[$this->arrayHandler->getChildMarker()]))
      {
        $this->checkSetValuesAsArray(
          $entry[$this->arrayHandler->getChildMarker()], $values);
      }
    }
  }

  /**
   * Pruefung, ob angegebener Eintrag an angegebener Stelle im Daten-Array
   * eingefuegt wurde
   *
   * @param array $data
   * @param array $newEntry
   * @param string $parentValue
   * @param string $insertBeforeId
   */
  protected function checkEntryInsertBeforeId($data, $newEntry, $parentValue,
    $insertBeforeId)
  {
    $this->assertInternalType('array', $data);
    foreach ($data as $entry)
    {
      $this->assertInternalType('array', $entry);

      if ($entry[$this->arrayHandler->getReferenceMarker()] == $parentValue)
      {
        // Child Element pruefen
        $this->assertArrayHasKey($this->arrayHandler->getChildMarker(), $entry);
        $this->assertInternalType('array', $entry[$this->arrayHandler->getChildMarker()]);

        $entryFound = false;
        $insertBefore = false;
        foreach ($entry[$this->arrayHandler->getChildMarker()] as $child)
        {
          if ($child == $newEntry)
          {
            $entryFound = true;
          }
          elseif ($entryFound == true
              && $child[$this->arrayHandler->getReferenceMarker()] == $insertBeforeId)
          {
            $insertBefore = true;
            break;
          }
          else
          {
            $entryFound = false;
          }
        }
        $this->assertTrue($entryFound);
        $this->assertTrue($insertBefore);
      }
      if (array_key_exists($this->arrayHandler->getChildMarker(), $entry))
      {
        $this->checkEntryInsertBeforeId($entry[$this->arrayHandler->getChildMarker()], 
          $newEntry, $parentValue, $insertBeforeId);
      }
    }
  }

  /**
   * Fuehrt die Pruefung der Daten nach dem Mergen aus
   *
   * @param array $data
   * @param array $testDatensatz
   */
  protected function checkMergeData($data, $testDatensatz)
  {
    foreach ($testDatensatz as $entry)
    {
      foreach ($data[$entry['id']] as $key => $value)
      {
        $this->assertSame($value, $entry[$key]);
      }
      if (isset($entry[$this->arrayHandler->getChildMarker()])
          && is_array($entry[$this->arrayHandler->getChildMarker()]))
      {
        $this->checkMergeData($data, $entry[$this->arrayHandler->getChildMarker()]);
      }
    }
  }

  /**
   * Fuehrt die Pruefung der Daten nach dem Loeschen eines Eintrags durch
   * 
   * @param array $testDatensatz  Testdatensatz
   * @param string $refValue  Wert, der geloescht werden sollte
   */
  protected function checkRemoveData($testDatensatz, $refValue)
  {
    $found = false;
    foreach ($testDatensatz as $entry)
    {
      if ($entry['id'] == $refValue)
      {
        $found = true;
      }
      elseif (isset($entry[$this->arrayHandler->getChildMarker()])
               && is_array($entry[$this->arrayHandler->getChildMarker()]))
      {
        $this->checkRemoveData($entry[$this->arrayHandler->getChildMarker()],
          $refValue);
      }
      $this->assertFalse($found);
    }
  }

  /**
   * Generiert ein Testdatensatz
   */
  protected function generateTestData()
  {
    $this->testDatensatz = array(
      array($this->arrayHandler->getReferenceMarker() => '100'),
      array($this->arrayHandler->getReferenceMarker() => '200',
        $this->arrayHandler->getChildMarker() => array(
          array($this->arrayHandler->getReferenceMarker() => '210'),
          array($this->arrayHandler->getReferenceMarker() => '220'),
          array($this->arrayHandler->getReferenceMarker() => '230')
        )
      ),
      array($this->arrayHandler->getReferenceMarker() => '300',
        $this->arrayHandler->getChildMarker() => array(
          array($this->arrayHandler->getReferenceMarker() => '310'),
          array($this->arrayHandler->getReferenceMarker() => '320',
            $this->arrayHandler->getChildMarker() => array(
              array($this->arrayHandler->getReferenceMarker() => '321'),
              array($this->arrayHandler->getReferenceMarker() => '322'),
              array($this->arrayHandler->getReferenceMarker() => '323')
            )
          ),
          array($this->arrayHandler->getReferenceMarker() => '330')
        )
      ),
    );
  }

  protected function generateInfoData(&$data, $testdatensatz)
  {
    foreach ($testdatensatz as $entry)
    {
      $data[$entry[$this->arrayHandler->getReferenceMarker()]] = array(
        'name' => 'NAME-' . md5(time()),
        'description' => 'DESC-' . md5(time())
      );
      if (isset($entry[$this->arrayHandler->getChildMarker()])
          && is_array($entry[$this->arrayHandler->getChildMarker()]))
      {
        $this->generateInfoData($data, $entry[$this->arrayHandler->getChildMarker()]);
      }
    }
  }
}