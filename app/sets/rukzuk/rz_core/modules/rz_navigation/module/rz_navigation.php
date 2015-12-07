<?php
namespace Rukzuk\Modules;

class rz_navigation extends SimpleModule
{

  /**
   * @var int
   */
  protected $numberOfTotalLevels = 5;

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\ModuleInfo            $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    // TODO: create dummy navigation (test navigation)
    $navigation = $renderApi->getNavigation();
    $nav = $this->getNavigationMarkup($renderApi, $unit, $navigation);
    if (is_object($nav)) {
      echo $nav->toString();
    } else if ($renderApi->isEditMode()) {
      // show hint when no children inserted
      $this->showNoPagesHint($renderApi, $unit, $moduleInfo);
    }
    $renderApi->renderChildren($unit);
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\ModuleInfo            $moduleInfo
   */
  protected function showNoPagesHint($renderApi, $unit, $moduleInfo)
  {
    $i18n = new Translator($renderApi, $moduleInfo);
    $msg = $i18n->translate('error.noPages');
    $errorTag = new HtmlTagBuilder('div', array(
      'class' => 'RUKZUKmissingInputHint'
    ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
    echo $errorTag->toString();
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\APIs\APIv1\Navigation $navigation
   *
   * @throws \Exception
   *
   * @return HtmlTagBuilder
   */
  protected function getNavigationMarkup($renderApi, $unit, $navigation)
  {
    $startPageId = $this->getStartPageId($renderApi, $unit, $navigation);
    $navigatorIds = $this->getNavigatorIds($navigation);

    return $this->getNavigationMarkupRecursive($renderApi, $unit, $navigation, $navigatorIds, $startPageId);
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\APIs\APIv1\Navigation $navigation
   * @param array                         $navigatorIds
   * @param string                        $pageId
   * @param int                           $level
   *
   * @throws \Exception
   *
   * @return HtmlTagBuilder
   */
  protected function getNavigationMarkupRecursive($renderApi, $unit, $navigation, array &$navigatorIds, $pageId, $level = 1)
  {
    if ($level > $this->getNumberOfTotalLevels()) {
      return null;
    }

    $childrenIds = $navigation->getChildrenIds($pageId);

    $levelEnabled = ($renderApi->getFormValue($unit, 'enableLevel' . $level, false) == true);
    $shouldIterateIntoSubLevels = $this->shouldIterateIntoSubLevels($renderApi, $unit, $level);

    $listItemTags = array();
    foreach ($childrenIds as $childrenId) {
      $childPage = $navigation->getPage($childrenId);

      if (!$childPage->showInNavigation()) {
        continue;
      }

      // show children?
      $childPageTag = null;
      if (!$shouldIterateIntoSubLevels || $renderApi->isTemplate() || $this->isItemActive($childrenId, $navigatorIds)) {
        $childPageTag = $this->getNavigationMarkupRecursive($renderApi, $unit, $navigation, $navigatorIds, $childrenId, $level + 1);
      }

      // show current item?
      if ($levelEnabled) {
        $listItemTags[] = $this->getNavItemMarkup($renderApi, $unit, $navigation, $navigatorIds, $childPage, $level, $childPageTag);
      } else {
        $listItemTags[] = $childPageTag;
      }
    }

    if (count($listItemTags) <= 0) {
      return null;
    }

    return new HtmlTagBuilder('ul', array('class' => 'navLevel' . $level), $listItemTags);
  }


  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\APIs\APIv1\Navigation $navigation
   * @param array                         $navigatorIds
   * @param \Render\APIs\APIv1\Page       $page
   * @param int                           $level
   * @param string                        $childrenMarkup
   *
   * @return string
   */
  protected function getNavItemMarkup($renderApi, $unit, $navigation, array &$navigatorIds, $page, $level, $childrenMarkup)
  {
    $pageId = $page->getPageId();

    $linkTag = new HtmlTagBuilder('a', array('class' => 'navLink'), array($page->getNavigationTitle()));
    $listTag = new HtmlTagBuilder('li', array('class' => 'navItem'), array($linkTag, $childrenMarkup));

    // get url
    if ($renderApi->isEditMode()) {
      $linkTag->set('href', 'javascript:void(0);');
    } else {
      $linkTag->set('href', $page->getUrl());
    }

    // collect classes
    if ($this->isItemActive($pageId, $navigatorIds)) {
      $listTag->addClass('navItemActive');
      $linkTag->addClass('navLinkActive');
    }
    if ($pageId == $navigation->getCurrentPageId()) {
      $listTag->addClass('navItemCurrent');
      $linkTag->addClass('navLinkCurrent');
    }

    return $listTag;
    // return $listTag->toString();
  }

  /**
   * Returns navigator ids (breadcrumbtrail) of current page
   *
   * @param \Render\APIs\APIv1\Navigation $navigation
   *
   * @return array
   */
  protected function getNavigatorIds($navigation)
  {
    $currentPage = $navigation->getPage($navigation->getCurrentPageId());
    $navigatorIds = $currentPage->getParentIds();
    $navigatorIds[] = $currentPage->getPageId();
    return $navigatorIds;
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\APIs\APIv1\Navigation $navigation
   *
   * @return string
   */
  protected function getStartPageId($renderApi, $unit, $navigation)
  {
    // determine navigation root (selected node or the root-node of the page tree); children of this node will be shown in navigation)
    $startPageId = $renderApi->getFormValue($unit, 'navStart');
    if (empty($startPageId) || !is_string($startPageId)) {
      return null;
    } else {
      return $startPageId;
    }
  }

  /**
   * @param string $pageId
   * @param array  $navigatorIds
   *
   * @return bool
   */
  protected function isItemActive($pageId, array &$navigatorIds)
  {
    return in_array($pageId, $navigatorIds);
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param int                           $level
   *
   * @return bool
   */
  protected function shouldIterateIntoSubLevels($renderApi, $unit, $level)
  {
    $maxLevel = $this->getNumberOfTotalLevels();
    $onlyShowActiveSubItems = false;
    $subLevel = $level;
    do {
      $subLevel++;
      $subLevelEnabled = ($renderApi->getFormValue($unit, 'enableLevel'.$subLevel, false) == true);
      if ($subLevelEnabled) {
        $onlyShowActiveSubItems = ($renderApi->getFormValue($unit, 'enableOnlyShowActiveItemsOfLevel' . ($subLevel), false) == true);
      }
    }
    while (!$subLevelEnabled && $subLevel <= $maxLevel) ;

    return $onlyShowActiveSubItems;
  }

  /**
   * @return int
   */
  protected function getNumberOfTotalLevels()
  {
    return $this->numberOfTotalLevels;
  }
}
