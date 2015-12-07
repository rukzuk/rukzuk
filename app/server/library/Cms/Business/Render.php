<?php
namespace Cms\Business;

use Seitenbau\Registry as Registry;

/**
 * Stellt die Business-Logik fuer Render zur Verfuegung
 *
 * @package      Cms
 * @subpackage   Business
 *
 * @method \Cms\Service\Render getService
 */

class Render extends Base\Service
{
  public function renderTemplate(
      $templateId,
      $websiteId,
      $renderContent,
      $mode,
      $codeType = null
  ) {
    $pageBusiness = $this->getBusiness('Page');

    $template = $this->getBusiness('Template')->getById($templateId, $websiteId);

    // Data hat hoehere Priorisierung als TemplateID
    if ($renderContent == '') {
      $renderContent = $template->getContent();
    }

    // Globale Variablen aus dem Content ermitteln
    $globalContent = $pageBusiness->getGlobalContentFromContent($websiteId, $renderContent, true);

    $this->getService()->renderTemplateContent(
        $renderContent,
        $websiteId,
        $templateId,
        $mode,
        $globalContent,
        $template,
        $codeType
    );
  }

  public function renderPage(
      $pageId,
      $websiteId,
      $renderContent,
      $mode,
      $ticket = null,
      $codeType = null
  ) {
    $pageBusiness = $this->getBusiness('Page');
    $modulService = $this->getService('Modul');

    $page = $pageBusiness->getById($pageId, $websiteId);
    
    if ($renderContent == '') {
      $renderContent = $page->getContent();
      $globalContent = $page->getGlobalContent();
      $pageBusiness->getService()->insertDefaultValuesIntoGlobalContent(
          $websiteId,
          $globalContent,
          $modulService
      );
    } else {
      // Daten ermitteln
      $renderContent = (is_string($renderContent))
                    ? \Zend_Json::decode($renderContent)
                    : $renderContent;

      // Globale Variablen aus dem Content ermitteln
      $globalContent = $pageBusiness->getGlobalContentFromContent($websiteId, $renderContent);
    }
    
    // ggf. Globale Variablen in Array umwandeln
    $globalContent = (is_string($globalContent))
                      ? \Zend_Json::decode($globalContent)
                      : $globalContent;

    // Page rendern
    $this->getService()->renderPageContent(
        $renderContent,
        $websiteId,
        $pageId,
        $mode,
        $globalContent,
        $page,
        $codeType
    );
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
      case 'page':
      case 'pageCss':
      case 'template':
      case 'templateCss':
            return $this->checkWebsitePrivilegeForIdentity($identity, $check['websiteId'], 'render', 'all');
        break;
    }
    
    // Default: Keine Rechte
    return false;
  }
}
