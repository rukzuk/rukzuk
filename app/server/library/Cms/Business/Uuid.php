<?php
namespace Cms\Business;

use Cms\Exception as CmsException;
use Seitenbau\Registry;
use Seitenbau\UniqueIdGenerator as UniqueIdGenerator;
use Orm\Iface\Data\Uuidable as Uuidable;

/**
 * Stellt die Business-Logik fuer Uuid zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Uuid extends Base\Service
{
  /**
   * Gibt Uuids einer angegebenen Klasse zurueck
   *
   * @param string  $dataClassName
   * @param integer $count
   * @throws Cms\Exception
   */
  public function getUuids($dataClassName, $count)
  {
    $dataClass = sprintf(
        "Orm\Data\%s",
        ucfirst($dataClassName)
    );

    if (!class_exists($dataClass)) {
      $errorMessage = sprintf("Data class '%s' does not exist", $dataClass);
      Registry::getLogger()->log(__METHOD__, __LINE__, $errorMessage, \Seitenbau\Log::ERR);
      throw new CmsException('1', __METHOD__, __LINE__);
    }

    $uuids = $this->generateUuids(new $dataClass, $count);

    return $uuids;
  }

  /**
   * Generiert die Uuids aus einer uebergebenen Klassen-Instanz
   *
   * @param Orm\Iface\Data\Uuidable $dataInstance
   * @param integer $count
   * @return array
   */
  private function generateUuids(Uuidable $dataInstance, $count)
  {
    $uuids = array();
    $uuidPrefix = $dataInstance::ID_PREFIX;
    $uuidSuffix = $dataInstance::ID_SUFFIX;
    if ($count > 0) {
      for ($i=0; $i < $count; ++$i) {
        $uuids[] = $uuidPrefix . UniqueIdGenerator::v4() . $uuidSuffix;
      }
    }
    return $uuids;
  }
}
