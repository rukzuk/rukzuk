<?php
namespace Cms\Business;

/**
 * Stellt die Business-Logik fuer Template zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 */

class Template extends Base\Service
{
  const TEMPLATE_CREATE_ACTION      = 'TEMPLATE_CREATE_ACTION';
  const TEMPLATE_DELETE_ACTION      = 'TEMPLATE_DELETE_ACTION';
  const TEMPLATE_EDIT_ACTION        = 'TEMPLATE_EDIT_ACTION';
  const TEMPLATE_EDIT_META_ACTION   = 'TEMPLATE_EDIT_META_ACTION';
  const TEMPLATE_COPY_PASTE_ACTION  = 'TEMPLATE_COPY_PASTE_ACTION';

  /**
   * @param string $id
   * @param string $websiteId
   * @param array  $columnsValues
   */
  public function update($id, $websiteId, array $columnsValues)
  {
    $template = $this->getService()->update($id, $websiteId, $columnsValues);

    $this->getBusiness('Reparse')->reparseTemplateLinkedPages(
        $template,
        $websiteId
    );

    return $template;
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
      case 'getById':
            return $this->isUserInAnyWebsiteGroup($identity, $check['websiteId']);
        break;
      
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
