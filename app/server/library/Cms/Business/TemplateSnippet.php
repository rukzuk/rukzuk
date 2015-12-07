<?php
namespace Cms\Business;

/**
 * Stellt die Business-Logik fuer TemplateSnippet zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class TemplateSnippet extends Base\Service
{
  const TEMPLATESNIPPET_CREATE_ACTION     = 'TEMPLATESNIPPET_CREATE';
  const TEMPLATESNIPPET_DELETE_ACTION     = 'TEMPLATESNIPPET_DELETE';
  const TEMPLATESNIPPET_EDIT_ACTION       = 'TEMPLATESNIPPET_EDIT';
  const TEMPLATESNIPPET_EDIT_META_ACTION  = 'TEMPLATESNIPPET_EDIT_META';



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
      case 'getById':
      case 'create':
      case 'delete':
      case 'edit':
        if ($this->checkUserGroupRights($identity, $check['websiteId'], 'templates', 'all')) {
          return true;
        }
            break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
