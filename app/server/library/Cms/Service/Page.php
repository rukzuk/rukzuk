<?php
namespace Cms\Service;

use Cms\Service\Base\Dao as DaoServiceBase;
use Cms\Exception as Exception;

/**
 * Stellt Service-Komponenten fuer Page zur Verfeugung
 *
 * @package      Cms
 * @subpackage   Service
 */

class Page extends DaoServiceBase
{
  var $moduleFormValues;
  
  /**
   * @param string  $id
   * @param string  $websiteId
   */
  public function getById($id, $websiteId)
  {
    return $this->execute('getById', array($id, $websiteId));
  }

  /**
   * Erzeugt eine Kopie einer vorhanden Page
   *
   * @param string  $id
   * @param string  $websiteId
   * @param string  $newname
   */
  public function copy($id, $websiteId, $newname = null)
  {
    return $this->execute('copy', array($id, $websiteId, $newname));
  }

  /**
   * Kopiert alle Pages einer Website zu einer anderen Website
   *
   * @param string $fromWebsiteId
   * @param string $toWebsiteId
   * @return true
   * @throws \Cms\Exception
   */
  public function copyPagesToNewWebsite($fromWebsiteId, $toWebsiteId)
  {
    $result = $this->execute(
        'copyPagesToNewWebsite',
        array($fromWebsiteId, $toWebsiteId)
    );
    return $result;
  }

  /**
   * Loescht eine Website anhand der id und websiteId
   *
   * @param  string  $id
   * @param  string  $websiteId
   * @return boolean
   */
  public function delete($id, $websiteId)
  {
    return $this->execute('delete', array($id, $websiteId));
  }
  /**
   * @param array  $ids
   * @param string $websiteId
   */
  public function deletePages(array $ids, $websiteId)
  {
    return $this->execute('deleteByIds', array($websiteId, $ids));
  }

  /**
   * @param string  $websiteid
   */
  public function deleteByWebsiteId($websiteId)
  {
    return $this->execute('deleteByWebsiteId', array($websiteId));
  }

  /**
   * Aktualisiert eine vorhandene Page
   *
   * @param string $id
   * @param string $websiteId
   * @param array $attributes
   */
  public function update($id, $websiteId, array $attributes)
  {
    if (isset($attributes['content']) && is_array($attributes['content'])) {
      $jsonAttributes = array('content');
      $attributes = $this->encodeJsonAttributesToJsonString(
          $attributes,
          $jsonAttributes
      );
    }
    if (isset($attributes['globalcontent']) && is_array($attributes['globalcontent'])) {
      $jsonAttributes = array('globalcontent');
      $attributes = $this->encodeJsonAttributesToJsonString(
          $attributes,
          $jsonAttributes
      );
    }

    return $this->execute('update', array($id, $websiteId, $attributes));
  }

  /**
   * erstellt eine neue website in der db
   *
   * @param string $websiteId
   * @param array $attributes
   */
  public function create($websiteId, $attributes)
  {
    if (isset($attributes['content']) && is_array($attributes['content'])) {
      $jsonAttributes = array('content');
      $attributes = $this->encodeJsonAttributesToJsonString(
          $attributes,
          $jsonAttributes
      );
    }
    if (isset($attributes['globalcontent']) && is_array($attributes['globalcontent'])) {
      $jsonAttributes = array('globalcontent');
      $attributes = $this->encodeJsonAttributesToJsonString(
          $attributes,
          $jsonAttributes
      );
    }

    return $this->execute('create', array($websiteId, $attributes));
  }

  /**
   * @param  string $id
   * @param  string $websiteId
   * @return boolean
   */
  public function existsPageAlready($id, $websiteId)
  {
    return $this->execute('existsPage', array($id, $websiteId));
  }

  /**
   * Gibt Namen der zur uebergebenen Website ID gehoerigen Pages zurueck
   *
   * @param int $websiteId
   */
  public function getInfosByWebsiteId($websiteId)
  {
    $result = null;

    $result = $this->execute('getInfosByWebsiteId', array($websiteId));

    return $result;
  }

  /**
   * Gibt Pages anhand der vorhandenen/verknuepften Media und Website Id
   * zurueck
   *
   * @param  string $mediaId
   * @param  string $websiteId
   * @return array
   */
  public function findByMediaAndWebsiteId($mediaId, $websiteId)
  {
    return $this->execute(
        'findByMediaAndWebsiteId',
        array($mediaId, $websiteId)
    );
  }

  /**
   * Liefert alle zum angegebenen Template verlinkte Pages zurueck
   *
   * @param string $templateId
   * @param string $websiteId
   * @param array
   */
  public function getTemplateLinkedPages($templateId, $websiteId)
  {
    return $this->execute(
        'getTemplateLinkedPages',
        array($templateId, $websiteId)
    );
  }
  
  /**
   * returns the page ids that have a relation with the given template id
   *
   * @param string $websiteId
   * @param string $templateId
   * @param array
   */
  public function getIdsByWebsiteIdAndTemplateId($websiteId, $templateId)
  {
    return $this->execute(
        'getIdsByWebsiteIdAndTemplateId',
        array($websiteId, $templateId)
    );
  }

  /**
   * returns all ids of pages related to the website given by website id
   *
   * @param string $websiteId
   * @return array
   */
  public function getIdsByWebsiteId($websiteId)
  {
    return $this->getDao()->getIdsByWebsiteId($websiteId);
  }

  /**
   * Aktualisiert die Default-Werte in den globalen Variablen
   *
   * @param string $websiteId
   * @param mixed  $globalcontent
   * @param \Cms\Service\Modul
   */
  public function insertDefaultValuesIntoGlobalContent(
      $websiteId,
      &$globalContent,
      \Cms\Service\Modul $moduleService
  ) {
    // ggf. Globale Variablen in Array umwandeln
    if (is_string($globalContent)) {
      $globalContent = \Seitenbau\Json::decode($globalContent);
    }
    
    if (is_array($globalContent) && count($globalContent) > 0) {
      foreach ($globalContent as $varName => &$units) {
        foreach ($units as &$unitData) {
          // Modul-Id vorhanden?
          if (isset($unitData['moduleId'])) {
          // Muss noch die globalen Variablen-Namen dieses Moduls ermittelt werden?
            if (isset($unitData['moduleId']) && !empty($unitData['moduleId'])
                && !isset($this->moduleFormValues[$websiteId][$unitData['moduleId']])) {
            // Form-Werte des Moduls ermitteln
              try {
                $module = $moduleService->getById($unitData['moduleId'], $websiteId);
                $formValues = $module->getFormvalues();
                if (is_string($formValues)) {
                  $formValues = \Seitenbau\Json::decode($formValues);
                }
                $this->moduleFormValues[$websiteId][$unitData['moduleId']] = $formValues;
              } catch (\Exception $e) {
              }
            }

            // Globale Felder vorhanden
            if (is_array($this->moduleFormValues[$websiteId][$unitData['moduleId']])
                && count($this->moduleFormValues[$websiteId][$unitData['moduleId']]) > 0 ) {
            // Modul-Wert des globalen Feldes vorhanden?
              if (isset($unitData['isUnitValue']) && !$unitData['isUnitValue']) {
                if (isset($this->moduleFormValues[$websiteId][$unitData['moduleId']][$varName])) {
                  $unitData['value'] = $this->moduleFormValues[$websiteId][$unitData['moduleId']][$varName];
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * @param  string $websiteId
   * @param  string $id
   * @return array
   */
  public function findByWebsiteIdAndModuleId($websiteId, $moduleId)
  {
    return $this->execute(
        'findByWebsiteIdAndModuleId',
        array($websiteId, $moduleId)
    );
  }
}
