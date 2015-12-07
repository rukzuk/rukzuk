<?php
namespace Cms\Business;

use \Seitenbau\Registry;
use \Cms\Service;

/**
 * Stellt die Business-Logik fuer Module zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\Modul getService
 */
class Modul extends Base\Service
{
  const MODUL_CREATE_ACTION = 'MODUL_CREATE_ACTION';
  const MODUL_DELETE_ACTION = 'MODUL_DELETE_ACTION';
  const MODUL_EDIT_ACTION = 'MODUL_EDIT_ACTION';
  const MODUL_EDIT_META_ACTION = 'MODUL_EDIT_META_ACTION';
  const MODUL_COPY_PAST_ACTION = 'MODUL_COPY_PAST_ACTION';
  const MODULE_CREATE_LOCAL_COPY_ACTION = 'MODULE_CREATE_LOCAL_COPY_ACTION';

  public function __construct($businessname)
  {
    parent::__construct($businessname);
  }

  /**
   * Liefert die Variablennamen der Globalen-Felder zurueck
   *
   * @param string $modulId
   * @param string $websiteId
   * @return  array   Liste mit Variablennamen der globalen Felder
   */
  public function getGlobalVariableNamesByModulId($modulId, $websiteId)
  {
    $globalVarName = array();
    $modul = $this->getById($modulId, $websiteId);
    $moduleForm       = $modul->getForm();
    $moduleFormValues = $modul->getFormvalues();
    if (isset($moduleForm) && is_array($moduleForm)) {
      foreach ($moduleForm as $formGroup) {
        if (property_exists($formGroup, 'formGroupData')) {
          $this->getGlobalVariableNames($formGroup->formGroupData, $moduleFormValues, $globalVarName);
        }
      }
    }
    return $globalVarName;
  }

  /**
   * Ermittelt aus dem uebergebenen FormField-Array die globalen Felder
   *
   * @param array $formFields
   * @param array $formFieldsValues
   * @param array $globalVars
   */
  private function getGlobalVariableNames($formFields, $formFieldsValues, &$globalVars)
  {
    if (is_array($formFields)) {
      foreach ($formFields as $formField) {
        // Ermitteln ob dieses Feld ein globales-Feld ist
        if (property_exists($formField, 'params') && is_array($formField->params)) {
          $foundCmsName     = false;
          $foundGlobalParam = false;
          $isGlobalVariable = null;
          $cmsVarName       = null;
          foreach ($formField->params as $variableParam) {
            if (property_exists($variableParam, 'name')) {
              if (!$foundCmsName && $variableParam->name == 'CMSvar') {
                $foundCmsName = true;
                $cmsVarName = ( property_exists($variableParam, 'value')
                                ? $variableParam->value
                                : $cmsVarName );
              } elseif (!$foundGlobalParam && $variableParam->name == 'isMeta') {
                $foundGlobalParam = true;
                $isGlobalVariable = ( property_exists($variableParam, 'value')
                                    ? $variableParam->value
                                    : $isGlobalVariable );
              }
            }

            // Alle benoetigte Parameter ermittelt
            if ($foundCmsName && $foundGlobalParam) {
              if ($isGlobalVariable) {
                // Bereits als Global fuer dieses Modul vorhanden?
                if (!isset($globalVars[$cmsVarName])) {
                // Default-Wert ermitteln
                  $defaultModuleValue = null;
                  if (is_object($formFieldsValues) && property_exists($formFieldsValues, $cmsVarName)) {
                    $defaultModuleValue = $formFieldsValues->$cmsVarName;
                  }
                  
                  // Globaler Name und Default-Wert aufnehmen
                  $globalVars[$cmsVarName] = array(
                    'name'    => $cmsVarName,
                    'default' => $defaultModuleValue,
                  );
                }
              }

              // Es werden keine weitern Parameter benoetigt -> Schleife abbrechen
              break;
            }
          }
        }

        // Sind in diesem Feld weiter untergeordnete Felder vorhanden
        if (property_exists($formField, 'items') && is_array($formField->items)) {
        // Untergeordnete Felder ueberpruefen
          $this->getGlobalVariableNames($formField->items, $formFieldsValues, $globalVars);
        }
      }
    }
  }

  /**
   * Pruefung, ob der angemeldete User die Rechte fuer die aufgerufene Aktion
   * besitzt
   *
   * @param array  $identity
   * @param string $rightname Name des Rechts, auf das geprueft wird
   * @param mixed  $check
   * @return boolean
   */
  protected function hasUserRights($identity, $rightname, $check)
  {
    // Superuser darf alles
    if ($this->isSuperuser($identity)) {
      return true;
    }
    
    switch ($rightname)
    {
      case 'getAll':
            return $this->isUserInAnyWebsiteGroup($identity, $check['websiteId']);
        break;

      case 'create':
      case 'delete':
      case 'edit':
        if ($this->checkUserGroupRights($identity, $check['websiteId'], 'modules', 'all')) {
          return true;
        }
            break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
