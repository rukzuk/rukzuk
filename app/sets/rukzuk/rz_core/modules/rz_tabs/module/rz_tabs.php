<?php
namespace Rukzuk\Modules;

class rz_tabs extends SimpleModule {

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo) {
    $tabTitles = $renderApi->getFormValue($unit, 'tabTitles', '');

    if (!empty($tabTitles)) {
      $tabTitles = explode("\n", $tabTitles);
      $this->renderTabBar($renderApi, $unit, $tabTitles);
      $this->renderTabContent($renderApi, $unit, $tabTitles);
    } else {
      $this->insertMissingInputHint($renderApi, $unit);
    }
  }

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param Array $tabTitles
   */
  protected function renderTabBar($renderApi, $unit, $tabTitles) {
    $tabCount = 0;
    foreach ($tabTitles as $tabTitle) {
      $tabId = $unit->getId() . '_tab' . $tabCount;
      $input = new HtmlTagBuilder('input', array(
        'type' => 'radio',
        'name' => $unit->getId(),
        'id' => $tabId
      ));

      if ($tabCount === 0) {
        $input->set('checked', null);
      }

      echo $input->toString();

      $label = new HtmlTagBuilder('label', array(
        'for' => $tabId,
        'class' => 'tabLabel'
      ), array($tabTitle));
      echo $label->toString();

      $tabCount++;
    }
  }

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param Array $tabTitles
   */
  protected function renderTabContent($renderApi, $unit, $tabTitles) {
    // get all children which are no extensions
    $children = array();
    foreach ($renderApi->getChildren($unit) as $childUnit) {
      if (!$renderApi->getModuleInfo($childUnit)->isExtension()) {
        $children[] = $childUnit;
      }
    }

    $tabsWrapper = new HtmlTagBuilder('div', array(
      'class' => 'tabsWrapper'
    ));
    echo $tabsWrapper->getOpenString();

    $tabCount = 0;
    foreach ($tabTitles as $tabTitle) {
      $tabWrapper = new HtmlTagBuilder('section', array('class' => 'tabContent'));
      echo $tabWrapper->getOpenString();

      $tabId = $unit->getId() . '_tab' . $tabCount;
      $label = new HtmlTagBuilder('label', array(
        'for' => $tabId,
        'class' => 'tabLabel'
      ), array(new HtmlTagBuilder('h2', null, $tabTitle)));
      echo $label->toString();

      if (!empty($children[$tabCount])) {
        $contentWrapper = new HtmlTagBuilder('div');
        echo $contentWrapper->getOpenString();
        $renderApi->renderUnit($children[$tabCount]);
        echo $contentWrapper->getCloseString();
      }

      echo $tabWrapper->getCloseString();

      $tabCount++;
    }

    echo $tabsWrapper->getCloseString();
  }
}
