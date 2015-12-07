<?php

namespace Orm\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140124142815 extends AbstractMigration
{
  public function up(Schema $schema)
  {
    // fixing website resolutions
    $stmt = $this->connection->query("SELECT id, resolutions FROM website");
    while ($website = $stmt->fetch()) {
      $this->extendAndSortResolutions($website['id'], $website['resolutions']);
    }
  }

  public function down(Schema $schema)
  {
    // do nothing
  }

  protected function extendAndSortResolutions($websiteId, $resolutionsJson)
  {
    $newResolutionsJson = $this->createResolutionsJson($resolutionsJson);
    $testData = json_decode($newResolutionsJson);
    if (!is_object($testData)) {
      return;
    }
    $this->connection->executeUpdate(
        'UPDATE website SET resolutions = :resolutions WHERE id = :id',
        array(
        'id' => $websiteId,
        'resolutions' => $newResolutionsJson,
        )
    );
  }

  /**
   * @return string
   */
  protected function getDefaultResolutionsJson()
  {
    return '{"enabled":false,"data":[]}';
  }

  /**
   * @param string $resolutionsJson
   *
   * @return string
   */
  protected function createResolutionsJson($resolutionsJson)
  {
    if (!is_string($resolutionsJson)) {
      return $this->getDefaultResolutionsJson();
    }

    $resolutions = json_decode($resolutionsJson);
    if (!is_object($resolutions)) {
      return $this->getDefaultResolutionsJson();
    }

    $existingIds = array();
    $newResolutions = json_decode($this->getDefaultResolutionsJson());

    if (property_exists($resolutions, 'enabled')) {
      $newResolutions->enabled = ($resolutions->enabled === true);
    }

    if (property_exists($resolutions, 'data')) {
      foreach ($resolutions->data as $resolution) {
        if (!is_object($resolution)) {
          continue;
        }
        if (property_exists($resolution, 'id')) {
          if (!is_string($resolution->id)) {
            unset($resolution->id);
          } else {
            $existingIds[] = $resolution->id;
          }
        }
        if (!property_exists($resolution, 'width')) {
          $resolution->width = 0;
        }
        $newResolutions->data[] = $resolution;
      }
    }

    usort($newResolutions->data, function ($a, $b) {
      if ($a->width == $b->width) {
        return 0;
      }
      // sort desc
      return ($a->width > $b->width) ? -1 : +1;
    });

    foreach ($newResolutions->data as &$resolution) {
      if (!property_exists($resolution, 'id')) {
        $resolution->id = $this->createNextResolutionId($existingIds);
        $existingIds[] = $resolution->id;
      }
      if (!property_exists($resolution, 'name')) {
        $resolution->name = $resolution->id;
      }
    }

    return json_encode($newResolutions);
  }

  /**
   * @param array $existingIds
   *
   * @return string
   */
  protected function createNextResolutionId(array $existingIds)
  {
    $count = 0;
    do {
      if ($count > 100) {
        return uniqid('resSec');
      }
      $newResId = 'res'.++$count;
    } while (in_array($newResId, $existingIds));
    return $newResId;
  }
}
