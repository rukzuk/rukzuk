<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130321104754 extends AbstractMigration
{
  const COLORID_PREFIX = 'COLOR-';
  const COLORID_SUFFIX = '-COLOR';

  /**
   * @var callable
   */
  protected $setColorIdToNewIdCallback = null;
  /**
   * @var callable
   */
  protected $setColorIdToOldIdCallback = null;
  /**
   * @var callable
   */
  protected $tablesToChange = array(
    array(
      'table'           => 'website',
      'idField'         => 'id',
      'idWebsiteField'  => 'id',
      'fields'          => array('colorscheme'),
    ),
    array(
      'table'           => 'modul',
      'idField'         => 'id',
      'idWebsiteField'  => 'websiteid',
      'fields'          => array('form', 'formvalues'),
    ),
    array(
      'table'           => 'template_snippet',
      'idField'         => 'id',
      'idWebsiteField'  => 'websiteid',
      'fields'          => array('content'),
    ),
    array(
      'table'           => 'template',
      'idField'         => 'id',
      'idWebsiteField'  => 'websiteid',
      'fields'          => array('content'),
    ),
    array(
      'table'           => 'page',
      'idField'         => 'id',
      'idWebsiteField'  => 'websiteid',
      'fields'          => array('content', 'templatecontent', 'globalcontent'),
    ),
  );
  
  
  public function up(Schema $schema)
  {
    $conn = $this->connection;

    foreach ($this->tablesToChange as $nextTable) {
      $this->changeColorIdsInDataFields($conn, $nextTable, false);
    }
  }


  public function down(Schema $schema)
  {
    $conn = $this->connection;

    foreach ($this->tablesToChange as $nextTable) {
      $this->changeColorIdsInDataFields($conn, $nextTable, true);
    }
  }

  protected function changeColorIdsInDataFields($conn, $table, $toOldId = false)
  {
    $query = sprintf(
        "SELECT %s, %s, %s FROM %s",
        $table['idWebsiteField'],
        $table['idField'],
        implode(', ', $table['fields']),
        $table['table']
    );
    $data = $conn->fetchAll($query);
    if (is_array($data)) {
      foreach ($data as $row) {
        foreach ($table['fields'] as $nextField) {
          $changeColorIdData = $row[$nextField];
          if ($toOldId) {
            $this->changeNewColorIdsToOldId($changeColorIdData);
          } else {
            $this->changeOldColorIdsToBaseId($changeColorIdData);
          }
          if ($row[$nextField] != $changeColorIdData) {
            $update = sprintf(
                'UPDATE %s SET %s = ? WHERE %s = ? AND %s = ?',
                $table['table'],
                $nextField,
                $table['idWebsiteField'],
                $table['idField']
            );
            $conn->executeUpdate($update, array(
              $changeColorIdData,
              $row[$table['idWebsiteField']],
              $row[$table['idField']]
            ));
          }
        }
      }
    }
  }

  protected function changeOldColorIdsToBaseId(&$mixedData)
  {
    $colorIdPrefix = self::COLORID_PREFIX;
    $colorIdSuffix = self::COLORID_SUFFIX;

    if (is_string($mixedData)) {
      if (!isset($this->setColorIdToNewIdCallback) || !is_callable($this->setColorIdToNewIdCallback)) {
        // create callback
        $this->setColorIdToNewIdCallback = function ($matches) use ($colorIdPrefix, $colorIdSuffix) {
          $baseColorId = $matches[0];
          $searchOldId = '/([^-])('.preg_quote($colorIdSuffix, '/').')$/';
          $baseColorId = preg_replace($searchOldId, '${1}--000000000000-${2}', $baseColorId);
          return $baseColorId;
        };
      }

      $regexpColorId = '/((' . preg_quote($colorIdPrefix, '/')
        . ')(.*?)(' . preg_quote($colorIdSuffix, '/') . '))/i';
      $mixedData = preg_replace_callback($regexpColorId, $this->setColorIdToNewIdCallback, $mixedData);
    } elseif (is_array($mixedData)) {
      foreach ($mixedData as $key => &$item) {
        // extending colorIds in item value
        $this->changeOldColorIdsToBaseId($item);
        
        // extending colorIds in key value
        $orgKey = $key;
        $this->changeOldColorIdsToBaseId($key);
        if ($orgKey != $key) {
          $mixedData[$key] = $mixedData[$orgKey];
          unset($mixedData[$orgKey]);
        }
      }
    }
  }
  

  protected function changeNewColorIdsToOldId(&$mixedData)
  {
    $colorIdPrefix = self::COLORID_PREFIX;
    $colorIdSuffix = self::COLORID_SUFFIX;

    if (is_string($mixedData)) {
      if (!isset($this->setColorIdToOldIdCallback) || !is_callable($this->setColorIdToOldIdCallback)) {
        // create callback
        $this->setColorIdToOldIdCallback = function ($matches) use ($colorIdPrefix, $colorIdSuffix) {
          $newColorId = $matches[0];
          $searchOldId = '/(\-\-[^-]+\-)('.preg_quote($colorIdSuffix, '/').')$/';
          $oldColorId = preg_replace($searchOldId, '${2}', $newColorId);
          return $oldColorId;
        };
      }

      $regexpColorId = '/((' . preg_quote($colorIdPrefix, '/')
        . ')(.*?)(' . preg_quote($colorIdSuffix, '/') . '))/i';
      $mixedData = preg_replace_callback($regexpColorId, $this->setColorIdToOldIdCallback, $mixedData);
    } elseif (is_array($mixedData)) {
      foreach ($mixedData as $key => &$item) {
        // extending colorIds in item value
        $this->changeNewColorIdsToOldId($item);
        
        // extending colorIds in key value
        $orgKey = $key;
        $this->changeNewColorIdsToOldId($key);
        if ($orgKey != $key) {
          $mixedData[$key] = $mixedData[$orgKey];
          unset($mixedData[$orgKey]);
        }
      }
    }
  }
}
