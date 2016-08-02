<?php
namespace Cms;

use Cms\Data\Page as Page;
use Cms\Data\Template as Template;
use Cms\Data\Modul as Modul;
use Orm\Data\Modul as DataModul;
use Dual\Render\CMS as CMS;
use Cms\Reparser\UnitExtractor;

/**
 * Reparser
 *
 * @package      Cms
 *
 */
class Reparser
{
  const TYPE_NEW = 'new';
  const TYPE_REPARSE = 'reparse';

  protected static $moduleBusiness = null;
  protected static $modules = array();

  /**
   * Page reparsen und updaten
   *
   * @param Cms\Data\Page     $page
   * @param Cms\Data\Template $template
   * @param string            $reparseType
   */
  public static function reparseAndUpdatePage(
      Page &$page,
      Template $template,
      $reparseType = self::TYPE_REPARSE
  ) {
    // Content reparsen
    $newPageContent = array();
    $newPageContent = self::reparseContent(
        $page->getWebsiteid(),
        $page->getContent(),
        $page->getTemplatecontent(),
        $template->getContent(),
        $reparseType
    );

    // Page mit den neuen Werten updaten
    $page->setContent(\Zend_Json::encode($newPageContent));
    $page->setTemplatecontent($template->getContent());
    $page->setTemplatecontentchecksum($template->getContentchecksum());

    // Page speichern
    $pageBusiness = new \Cms\Business\Page('Page');
    $pageBusiness->update(
        $page->getId(),
        $page->getWebsiteId(),
        array('content' => \Zend_Json::encode($newPageContent),
        'templatecontent' => $template->getContent(),
        'templatecontentchecksum' => $template->getContentchecksum())
    );

    return true;
  }

  /**
   * Content einer Page reparsen
   *
   * @param string $websiteId
   * @param string $pageContentAsJson
   * @param string $pageTemplateContentAsJson
   * @param string $templateContentAsJson
   * @param string $reparseType
   */
  public static function reparseContent(
      $websiteId,
      $pageContentAsJson,
      $pageTemplateContentAsJson,
      $templateContentAsJson,
      $reparseType = self::TYPE_REPARSE
  ) {
    $pageUnits = UnitExtractor::getUnitsFromPageContent($pageContentAsJson);
    $pageTemplateUnits = UnitExtractor::getUnitsFromTemplateContent($pageTemplateContentAsJson);
    $templateUnits = UnitExtractor::getUnitsFromTemplateContent($templateContentAsJson);

    // Alle Units der Page durchlaufen und Reparsen
    $newPageUnits = array('units' => array(), 'tunits' => array());
    if (is_array($pageUnits['units'])) {
      foreach ($pageUnits['units'] as $unitId => &$pageUnit) {
        // Template Unit-ID vorhanden
        if (isset($pageUnit['templateUnitId'])
          && isset($pageTemplateUnits['units'][$pageUnit['templateUnitId']])
          && isset($templateUnits['units'][$pageUnit['templateUnitId']])
        ) {
          $newPageUnit = array();
          $state = self::reparseUnit($newPageUnit, $pageUnit, $pageTemplateUnits['units'][$pageUnit['templateUnitId']], $templateUnits['units'][$pageUnit['templateUnitId']], $reparseType);
          if ($state) {
            // Unit aufnehmen
            $newPageUnits['units'][$unitId] = $newPageUnit;
            if (!isset($newPageUnits['tunits'][$pageUnit['templateUnitId']])) {
              $newPageUnits['tunits'][$pageUnit['templateUnitId']] = array();
            }
            $newPageUnits['tunits'][$pageUnit['templateUnitId']][$unitId] =& $newPageUnits['units'][$unitId];
          }
        } // Template Unit-IDs vorhanden
        elseif (!isset($pageUnit['templateUnitId']) || empty($pageUnit['templateUnitId'])) {
          // Fehler -> Nicht Reparsen
          // Template Unit-IDs muessen in den Page-Units vorhanden sein, damit
          // die Zuordnung der Page-Units zu den Template-Units hergestellt
          // werden kann
          $pageUnitId = (isset($pageUnit['id']) && $pageUnit != '')
            ? $pageUnit['id'] : 'unknown';
          $pageUnitName = (isset($pageUnit['name']) && $pageUnit != '')
            ? $pageUnit['name'] : 'unknown';
          $data = array(
            'unitId' => $pageUnitId,
            'unitName' => $pageUnitName
          );
          throw new Exception(801, __METHOD__, __LINE__, $data);
        }
      }
    }

    // Die Struktur reparsen
    $templateContent = \Zend_Json::decode($templateContentAsJson);
    $newPageContent = array();
    $state = self::reparseStructure($websiteId, $newPageContent, $templateContent, $pageUnits, $newPageUnits, $pageTemplateUnits, $reparseType);

    return $newPageContent;
  }

  // Units Reparsen
  protected static function reparseUnit(
      &$newPageUnit,
      $pageUnit,
      $orgTemplateUnit,
      $templateUnit,
      $reparseType
  ) {
    if (!is_array($pageUnit)) {
      return true;
    }

    // Zu ignorierende Attribute
    $ignoreAttributes = array(
      'children',
      'ghostChildren',
    );

    // Attribute welche immer aus der Page kommen
    $pageAttributes = array(
      'id',
      'templateUnitId',
    );

    // Attribute welche immer aus dem Template kommen
    $templateAttributes = array(
      'moduleId',
      'deletable',
      'readonly',
      'ghostContainer',
      'visibleFormGroups',
      'icon',
      'htmlClass'
    );

    $allKeys = self::getUnitValueArrayKeys($pageUnit, $templateUnit);
    $newPageUnit = array();
    foreach ($allKeys as $key) {
      if (in_array($key, $ignoreAttributes)) {
        continue;
      }
      if (in_array($key, $pageAttributes)) {
        $newPageUnit[$key] = $pageUnit[$key];
        continue;
      }
      if (in_array($key, $templateAttributes)) {
        if (array_key_exists($key, $templateUnit)) {
          $newPageUnit[$key] = $templateUnit[$key];
        }
        continue;
      }
      self::reparseUnitValue(
          $key,
          $newPageUnit,
          self::getUnitValueState($key, $pageUnit),
          self::getUnitValueState($key, $orgTemplateUnit),
          self::getUnitValueState($key, $templateUnit)
      );
    }

    // Erfolgreich
    return true;
  }

  /**
   * Reparse the unit values
   *
   * @link http://www.plantuml.com/plantuml/svg/fPCnReGm44LxJZ5IYeLc3x1RH95oW1J90IPumACOW-p94cuVcmLCWAr2PTcVUTyFuGMTP02EEiAu0E0AyWvhWXkQRmBwOI_-kG3HP24Vo1TZR1SiDTgQr55obsKFw1mEns4odY2tRTe1U7ePdyjR6gBHmoU5joVoUHN_21CIxxewKIHOwhH3Ld6LFG8VO1KhUv8zD2A5ciwWJD5pelob9kQM63sjlGCAj8vhjcYMwSVMvghlEjZT67BKj3TIKBcs6Rsasg_iItHgAsfJ-A6OaHO_9rZg9spFejd6Zalb_sfQLfUQ1D0O-AB1tuEs3PE28orPGioHTEMf75gruS-4-o1O_WReDPjmRHPoCHmCkcLkyGi0
   *
   * <code>
   * @startuml
   * start
   * repeat
   * if (page value exists?) then (yes)
   * if (page value changed?) then (yes)
   * if (page value is array?) then (yes)
   * else (no)
   * #AAAAAA:Page value has been changed.
   * Use page value;
   * detach;
   * endif
   * else (no)
   * #AAAAAA:Page value didn't changed.
   * Use template value.;
   * detach;
   * endif
   * else (no)
   * if (value exists at original template?) then (yes)
   * #AAAAAA:This value has been removed from page.
   * Discard value;
   * detach;
   * else (no)
   * #AAAAAA:This is a new value within the template.
   * Use template value.;
   * detach;
   * endif
   * endif
   * :get all keys;
   * note right
   * Use all keys from page value and template value
   * end note
   * repeat while (iterate over all keys)
   * @enduml
   * </code>
   *
   * @param string $key
   * @param array $newPageValue
   * @param array $orgPageValueState
   * @param array $orgTemplateValueState
   * @param array $newTemplateValueState
   */
  protected static function reparseUnitValue($key, &$newPageValue, $orgPageValueState, $orgTemplateValueState, $newTemplateValueState)
  {
    // if page value didn't changed, use value from template
    if ($orgPageValueState['type'] == $orgTemplateValueState['type'] &&
      $orgPageValueState['value'] == $orgTemplateValueState['value']) {
      if ($newTemplateValueState['exists']) {
        $newPageValue[$key] = $newTemplateValueState['value'];
      }
      return;
    }

    // if page value didn't exists in original template and page, add value from template
    if (!$orgPageValueState['exists'] && !$orgTemplateValueState['exists']) {
      if ($newTemplateValueState['exists']) {
        $newPageValue[$key] = $newTemplateValueState['value'];
      }
      return;
    }

    // if page value removed, no value should be taken over
    if (!$orgPageValueState['exists']) {
      return;
    }

    // if values not arrays, use page values
    if (!is_array($orgPageValueState['value'])) {
      $newPageValue[$key] = $orgPageValueState['value'];
      return;
    }

    // reparse arrays
    $newPageValue[$key] = array();
    $allKeys = self::getUnitValueArrayKeys($orgPageValueState['value'], $newTemplateValueState['value']);
    foreach ($allKeys as $subKey) {
      self::reparseUnitValue(
          $subKey,
          $newPageValue[$key],
          self::getUnitValueState($subKey, $orgPageValueState['value']),
          self::getUnitValueState($subKey, $orgTemplateValueState['value']),
          self::getUnitValueState($subKey, $newTemplateValueState['value'])
      );
    }
  }

  /**
   * @param $orgPageValue
   * @param $newTemplateValue
   *
   * @return array
   */
  protected static function getUnitValueArrayKeys($orgPageValue, $newTemplateValue)
  {
    $allKeys = array();
    if (is_array($orgPageValue)) {
      $allKeys = array_keys($orgPageValue);
    }
    if (is_array($newTemplateValue)) {
      $allKeys = array_unique(array_merge($allKeys, array_keys($newTemplateValue)));
      return $allKeys;
    }
    return $allKeys;
  }

  /**
   * @param string $key
   * @param mixed  $unitValue
   *
   * @return array
   */
  protected static function getUnitValueState($key, $unitValue)
  {
    $state = array(
      'exists' => false,
      'type' => null,
      'value' => null,
    );
    if (is_array($unitValue)) {
      if (isset($unitValue[$key]) || array_key_exists($key, $unitValue)) {
        $state['exists'] = true;
        $state['value'] = $unitValue[$key];
        $state['type'] = gettype($state['value']);
      }
    }
    return $state;
  }

  /**
   * Reparsen der Struktur
   */
  protected static function reparseStructure(
      $websiteId,
      &$newPageContent,
      &$templateContent,
      &$pageUnits,
      &$newPageUnits,
      &$pageTemplateUnits,
      $reparseType,
      $insideGhost = false
  ) {
    if (is_array($templateContent)) {
      // Templatestruktur durchlaufen, die neue Struktur aufbauen und die reparsten Units einhaengen
      foreach ($templateContent as &$templateUnit) {
        // Befinden wir uns innerhalb eines Ghostcontainers?
        if ($insideGhost) {
          // Erweiterungsmodule werden in einem Ghostcontainers immer eingefuegt bzw. reparsed
          try {
            // moduleType der Unit ermitteln
            $moduleType = self::getModuleType($websiteId, $templateUnit['moduleId']);
          } catch (\Exception $e) {
            $data = array(
              'unitId' => (isset($templateUnit['id']) ? $templateUnit['id'] : 'unknown'),
              'unitName' => (isset($templateUnit['name']) ? $templateUnit['name'] : 'unknown'),
              'moduleId' => (isset($templateUnit['moduleId']) ? $templateUnit['moduleId'] : 'unknown'),
            );
            throw new Exception(802, __METHOD__, __LINE__, $data);
          }
          // Kein Erweiterungsmodules?
          if ($moduleType != CMS::MODULE_TYPE_EXTENSION) {
            // Nur Erweiterungsmodule werden eingefuegt als waeren wir
            // NICHT in einem ghostContainer
            // Alle anderen moduleTypen werden ueber die Funktion
            // 'reparseOptionalStructure' eingefuegt und somit hier uebersprungen
            continue;
          }
        }

        // Unit in der bestehenden Seite vorhanden?
        $newPageUnit = null;
        if (!self::findUnitForReparse($newPageUnits, $templateUnit['id'], $newPageUnit, $reparseType)) {
          // Unit in der Page nicht vorhanden

          // Sind wir im repasen und nicht beim neu Anlegen und
          // ist diese Unit loeschbar und wurde die Unit in der Page entfernt?
          if (// reparsen?
            $reparseType !== self::TYPE_NEW
            // Unit loeschbar?
            && isset($templateUnit['deletable'])
            && $templateUnit['deletable'] === true
            // Unit im alten Template bereits vorhanden?
            && isset($pageTemplateUnits['units'])
            && isset($pageTemplateUnits['units'][$templateUnit['id']])
          ) {
            // Ganzer Ast nicht beachten, da dieser absichtlich aus der Page geloescht wurde
            continue;
          }

          // Neue Unit im Template -> auch in die neue Page einfuegen
          //  -> Werte uebernehmen
          //  -> TemplateUnit-Id uebernehmen
          //  -> Neue WebpageUnit-Id erzeugen

          // Neue Page-Unit erstellen
          $newPageUnit = self::createPageUnit($templateUnit, $reparseType);
        }

        // Kindelemente reparsen
        $orgPageOptionalUnit = null;
        $ret = self::reparseChildernStructure(
            $websiteId,
            $orgPageOptionalUnit,
            $newPageUnit,
            $templateUnit,
            $pageUnits,
            $newPageUnits,
            $pageTemplateUnits,
            $reparseType
        );
        if ($ret === false) {
          // Fehler
          return false;
        }

        // evtl. Markierung "Unit bereits reparsed" entfernen
        if (isset($newPageUnit['reparsed'])) {
          unset($newPageUnit['reparsed']);
        }

        // Markierung "optional eingefuegte Unit" entfernen
        if (isset($newPageUnit['inserted'])) {
          unset($newPageUnit['inserted']);
        }

        // Unit aufnehmen
        $newPageContent[] = $newPageUnit;
      }
    }

    // Erfolgreich
    return true;
  }
  // Optionale Struktur Reparsen

  protected static function reparseOptionalStructure(
      $websiteId,
      &$newPageContent,
      &$orgPageContent,
      &$pageUnits,
      &$newPageUnits,
      &$ghostUnits,
      &$templateGhostUnits,
      &$pageTemplateUnits,
      $reparseType
  ) {
    if (is_array($orgPageContent)) {
      // Optionale Units durchlaufen, die neue Struktur aufbauen und die reparsten Units einhaengen
      foreach ($orgPageContent as &$orgPageUnit) {
        // init
        $newPageUnit = null;

        // moduleTyp der Unit ermitteln
        try {
          $moduleType = self::getModuleType($websiteId, $orgPageUnit['moduleId']);
        } catch (\Exception $e) {
          $data = array(
            'unitId' => (isset($orgPageUnit['id']) ? $orgPageUnit['id'] : 'unknown'),
            'unitName' => (isset($orgPageUnit['name']) ? $orgPageUnit['name'] : 'unknown'),
            'moduleId' => (isset($orgPageUnit['moduleId']) ? $orgPageUnit['moduleId'] : 'unknown'),
          );
          throw new Exception(802, __METHOD__, __LINE__, $data);
        }
        // Ist dies eine Unit eines Erweiterungsmodules?
        if ($moduleType == CMS::MODULE_TYPE_EXTENSION) {
          // Erweiterungsmodule wurden bereist aufgenommen und sind somit
          // keine optionalen Elemente
          continue;
        }

        // Is die Unit nach dem Reparsen ueberhaupt noch vorhanden?
        if (isset($newPageUnits['units'][$orgPageUnit['id']])) {
          // Unitdaten aufnehmen
          $newPageUnit = $newPageUnits['units'][$orgPageUnit['id']];

          // Darf dieses Modul ueberhaupt auf dieser Ebene eingefuegt werden
          if (!isset($ghostUnits['tunits'][$newPageUnit['templateUnitId']])
            || !is_array($ghostUnits['tunits'][$newPageUnit['templateUnitId']])
            || !(count($ghostUnits['tunits'][$newPageUnit['templateUnitId']]) > 0)
          ) {
            // Nein -> Modul nicht aufnehmen
            continue;
          }

          // Template-Unit ermitteln
          //reset($ghostUnits['tunits'][$newPageUnit['templateUnitId']]);
          //$templateUnit = current($ghostUnits['tunits'][$newPageUnit['templateUnitId']]);
          if (is_array($templateGhostUnits)) {
            foreach ($templateGhostUnits as &$nextTemplateGhostUnits) {
              if (isset($nextTemplateGhostUnits['id'])
                && $newPageUnit['templateUnitId'] == $nextTemplateGhostUnits['id']
              ) {
                $templateUnit = $nextTemplateGhostUnits;
                break;
              }
            }
          }

          // Kindelemente reparsen
          $ret = self::reparseChildernStructure(
              $websiteId,
              $orgPageUnit,
              $newPageUnit,
              $templateUnit,
              $pageUnits,
              $newPageUnits,
              $pageTemplateUnits,
              $reparseType
          );
          if ($ret === false) {
            // Fehler
            return false;
          }
        }

        // Unit aufnehmen
        if (isset($newPageUnit)) {
          // evtl. Markierung "Unit bereits reparsed" entfernen
          if (isset($newPageUnit['reparsed'])) {
            unset($newPageUnit['reparsed']);
          }

          // Als optional eingefuegte Unit markieren
          $newPageUnit['inserted'] = true;

          // Unit aufnehmen
          $newPageContent[] = $newPageUnit;
        }
      }
    }

    // Erfolgreich
    return true;
  }
  // Kindelemente einer Unit reparsen

  protected static function reparseChildernStructure(
      $websiteId,
      &$orgPageOptionalUnit,
      &$newPageUnit,
      &$templateUnit,
      &$pageUnits,
      &$newPageUnits,
      &$pageTemplateUnits,
      $reparseType
  ) {
    // Kindelemente vorhanden?
    if (isset($templateUnit['children'])
      && is_array($templateUnit['children']) && count($templateUnit['children']) > 0
    ) {
      // Kinder zuruecksetzen
      $newPageUnit['children'] = array();

      // Sind die Kindelemente optional?
      $ghostContainer = (isset($templateUnit['ghostContainer'])
      && $templateUnit['ghostContainer']
        ? true : false);


      // Als erstes die nicht 'optionalen Kindelemente' aufnehmen
      // Die ist bei Kindelementen der Fall wenn:
      // wir nicht in einem ghostContainer sind oder
      // wenn sich Elemente mit dem ModulTyp 'extension' innerhalb eines ghostContainers befinden
      $ret = self::reparseStructure($websiteId, $newPageUnit['children'], $templateUnit['children'], $pageUnits, $newPageUnits, $pageTemplateUnits, $reparseType, $ghostContainer);
      if ($ret === false) {
        // Fehler
        return false;
      }

      // Kindelemente als Ghost aufnehmen?
      if ($ghostContainer) {
        // In der Page kommen diese Units in den seperaten ghost Bereich
        $newPageUnit['ghostChildren'] = array();
        self::createGhostChildrenStructure(
            $websiteId,
            $newPageUnit['ghostChildren'],
            $templateUnit['children'],
            $pageTemplateUnits,
            $reparseType
        );

        // evtl. Optionale Elemente ermitteln
        if (!isset($orgPageOptionalUnit)) {
          self::findUnitForReparse($pageUnits, $templateUnit['id'], $orgPageOptionalUnit, $reparseType);
        }

        // Optionale Kindunits vorhanden?
        if (isset($orgPageOptionalUnit['children'])
          && is_array($orgPageOptionalUnit['children'])
          && count($orgPageOptionalUnit['children']) > 0
        ) {
          // Duerfen ueberhaupt optionale Units unter dieser Unit vorhanden sein
          if (isset($templateUnit['children'])
            && is_array($templateUnit['children']) && count($templateUnit['children']) > 0
          ) {
            // Alle optionalen Elemente ermitteln
            $nextGhostUnits = array('units' => array(), 'tunits' => array());
            UnitExtractor::getUnitsFromContent(
                $newPageUnit['ghostChildren'],
                $nextGhostUnits,
                true,
                false,
                true
            );

            // Vorhandene optionale Units wieder einfuegen
            $ret = self::reparseOptionalStructure($websiteId, $newPageUnit['children'], $orgPageOptionalUnit['children'], $pageUnits, $newPageUnits, $nextGhostUnits, $templateUnit['children'], $pageTemplateUnits, $reparseType);
            if ($ret === false) {
              // Fehler
              return false;
            }
          }
        }
      }
    }

    // reparsen der Kindelemente erfolgreich
    return true;
  }
  // Ghostchildren anhand des Template erstellen

  protected static function createGhostChildrenStructure(
      $websiteId,
      &$newPageGhostChildren,
      &$templateChildren,
      $pageTemplateUnits,
      $reparseType
  ) {
    // Kindunits der Templateunit durchlaufen und alle Units, ausser Units
    // mit moduleTyp 'extention', als 'ghostChildren' aufnehmen
    if (isset($templateChildren) && is_array($templateChildren)) {
      foreach ($templateChildren as &$templateUnit) {
        // moduleTyp der Unit ermitteln und Erweiterungsmodule
        // NICHT als 'ghostChildren' eingefuegt
        try {
          $moduleType = self::getModuleType($websiteId, $templateUnit['moduleId']);
        } catch (\Exception $e) {
          $data = array(
            'unitId' => (isset($templateUnit['id']) ? $templateUnit['id'] : 'unknown'),
            'unitName' => (isset($templateUnit['name']) ? $templateUnit['name'] : 'unknown'),
            'moduleId' => (isset($templateUnit['moduleId']) ? $templateUnit['moduleId'] : 'unknown'),
          );
          throw new Exception(802, __METHOD__, __LINE__, $data);
        }
        // Erweiterungsmodules?
        if ($moduleType == CMS::MODULE_TYPE_EXTENSION) {
          // Erweiterungsmodule werden NICHT als 'ghostChildren' eingefuegt
          continue;
        }

        // Neue Page-Unit erstellen
        $newPageGhostUnit = self::createPageUnit($templateUnit, $reparseType);

        // Kindelemente reparsen
        $orgPageOptionalUnit = $pageUnits = $newPageUnits = null;
        $ret = self::reparseChildernStructure(
            $websiteId,
            $orgPageOptionalUnit,
            $newPageGhostUnit,
            $templateUnit,
            $pageUnits,
            $newPageUnits,
            $pageTemplateUnits,
            $reparseType
        );
        if ($ret === false) {
          // Fehler
          return false;
        }

        // evtl. Markierung "Unit bereits reparsed" entfernen
        if (isset($newPageGhostUnit['reparsed'])) {
          unset($newPageGhostUnit['reparsed']);
        }

        // Markierung "optional eingefuegte Unit" entfernen
        if (isset($newPageGhostUnit['inserted'])) {
          unset($newPageGhostUnit['inserted']);
        }

        // Unit aufnehmen
        $newPageGhostChildren[] = $newPageGhostUnit;
      }
    }

    // Erfolgreich repatsed
    return true;
  }
  // Page-Unit anhand einer Template-Unit erstellen

  protected static function createPageUnit($templateUnit, $reparseType)
  {
    // Page-Unit erstellen
    //  -> Werte uebernehmen
    //  -> TemplateUnit-Id uebernehmen
    //  -> Neue PageUnit-Id erzeugen
    if (is_array($templateUnit)) {
      // Alle Werte bis auf die Kindelemente uebernehmen
      $newPageUnit = array();
      foreach ($templateUnit as $key => $value) {
        // id, children und ghostChildren nicht uebernehmen
        if (!($key == 'id' || $key == 'children' || $key == 'ghostChildren')) {
          $newPageUnit[$key] = $value;
        }
      }
      // TemplateUnit-Id uebernehmen
      $newPageUnit['templateUnitId'] = $templateUnit['id'];

      // Neue WebpageUnit-Id erstellen
      $newPageUnit['id'] = static::createNewPageUnitId($templateUnit);
    } else {
      // Wert direkt uebernehmen
      $newPageUnit = $templateUnit;
    }

    // KinderUnits zuruecksetzen
    $newPageUnit['children'] = array();

    // Neue Unit zurueckgeben
    return $newPageUnit;
  }
  // Unit finden und

  protected static function findUnitForReparse(
      &$newPageUnits,
      $templateUnitId,
      &$newPageUnit,
      $reparseType
  ) {
    if (isset($newPageUnits) && is_array($newPageUnits)
      && isset($newPageUnits['tunits']) && is_array($newPageUnits['tunits'])
      && isset($newPageUnits['tunits'][$templateUnitId])
    ) {
      // Unit suchen
      if (is_array($newPageUnits['tunits'][$templateUnitId])) {
        foreach ($newPageUnits['tunits'][$templateUnitId] as $curKey => &$nextFoundNewPageUnit) {
          if (!((isset($nextFoundNewPageUnit['insideGhost']) && $nextFoundNewPageUnit['insideGhost'])
            ||
            (isset($nextFoundNewPageUnit['reparsed']) && $nextFoundNewPageUnit['reparsed'])
          )
          ) {
            // Unit gefunden
            $newPageUnit = $newPageUnits['tunits'][$templateUnitId][$curKey];

            // Nicht benoetgte Attribute entfernen
            unset($newPageUnit['reparsed']);
            unset($newPageUnit['insideGhost']);

            // Unit als Reparsed markieren
            $nextFoundNewPageUnit['reparsed'] = true;

            return true;
          }
        }
      }
    }

    // Unit nicht gefunden
    return false;
  }
  // Modul anhand der Id ermitteln

  protected static function getModuleBusiness()
  {
    if (!isset(self::$moduleBusiness)
      || !(self::$moduleBusiness instanceof \Cms\Business\Modul)
    ) {
      self::$moduleBusiness = new \Cms\Business\Modul('Modul');
    }

    return self::$moduleBusiness;
  }
  // Modul anhand der Id ermitteln

  protected static function getModuleById($websiteId, $moduleId)
  {
    // Website-Array vorhanden
    if (!isset(self::$modules[$websiteId])
      || !is_array(self::$modules[$websiteId])
    ) {
      self::$modules[$websiteId] = array();
    }

    // Modul vorhanden?
    if (!isset(self::$modules[$websiteId][$moduleId])
      || !(self::$modules[$websiteId][$moduleId] instanceof Modul)
    ) {
      // Modul neu ermitteln
      self::$modules[$websiteId][$moduleId] = static::getModuleBusiness()->getById($moduleId, $websiteId);
    }

    return self::$modules[$websiteId][$moduleId];
  }
  // Modul Typ anhand der ModuleId ermitteln

  protected static function getModuleType($websiteId, $moduleId)
  {
    $module = self::getModuleById($websiteId, $moduleId);
    return $module->getModuletype();
  }

  /**
   * @param $templateUnit
   *
   * @return string
   */
  protected static function createNewPageUnitId($templateUnit)
  {
    return \Orm\Data\Page\MUnit::ID_PREFIX .
      \Seitenbau\UniqueIdGenerator::v4() .
      \Orm\Data\Page\MUnit::ID_SUFFIX;
  }
}
