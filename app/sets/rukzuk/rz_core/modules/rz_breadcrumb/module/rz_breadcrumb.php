<?php
namespace Rukzuk\Modules;

class rz_breadcrumb extends SimpleModule
{

  protected function renderContent($api, $unit, $moduleInfo)
  {
    $items = array();

    // get breadcrumb trail
    if ($api->isPage()) {
      $startPageId = $api->getFormValue($unit, 'navStart');

      // get all page ids in current navigator
      $nav = $api->getNavigation();
      $pageIds = $nav->getNavigatorIds($nav->getCurrentPageId());

      // only show until start page if set
      if (!empty($startPageId) && in_array($startPageId, $pageIds)) {
        $pageIds = array_slice($pageIds, array_search($startPageId, $pageIds) + 1);
      }

      if (is_array($pageIds)) {
        foreach ($pageIds as $pageId) {
          $page = $nav->getPage($pageId);

          // echo "\nPAGE: {$page->getTitle()} {$page->getUrl()}";

          $items[] = array(
            'href' => $api->isEditMode() ? 'javascript:void(0)' : $page->getUrl(),
            'title' => $page->getNavigationTitle()
          );
        }
      }
    } else {
      if ($api->isEditMode() || $api->isPreviewMode()) {
        // demo nav items in preview and edit mode
        $i18n = new Translator($api, $moduleInfo);
        $items = array(
          array('href' => 'javascript:void(0)', 'title' => $i18n->translate('testdata.page') . ' 1'),
          array('href' => 'javascript:void(0)', 'title' => $i18n->translate('testdata.page') . ' 2'),
          array('href' => 'javascript:void(0)', 'title' => $i18n->translate('testdata.page') . ' 3')
        );
      }
    }

    $spacerTag = $this->getBreadcrumbSpacerTag($api->getFormValue($unit, 'spacer'));
    $trail = array();
    foreach ($items as $page) {
      $trail[] = $this->getBreadcrumbItemTag($page)->toString();
    }
    echo implode($spacerTag->toString(), $trail);

    $api->renderChildren($unit);
  }

  protected function getBreadcrumbItemTag($page)
  {
    return new HtmlTagBuilder('a', array(
      'href' => $page['href'],
      'class' => 'breadcrumbNavLink'
    ), array($page['title']));
  }

  protected function getBreadcrumbSpacerTag($spacer)
  {
    return new HtmlTagBuilder('span', array(
      'class' => 'breadcrumbNavSpacer'
    ), array($spacer));
  }
}
