<?php
namespace Rukzuk\Modules;

class rz_anchor_navigation extends SimpleModule {


  protected function modifyWrapperTag($tag, $api, $unit, $moduleInfo)
  {
    $tag->set(array(
      'data-scroll-speed' => $api->getFormValue($unit, 'scrollSpeed'),
      'data-scroll-easing' => $api->getFormValue($unit, 'scrollEasing'),
      'data-update-location-hash' => !$api->isEditMode()
    ));
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  protected function renderContent($api, $unit, $moduleInfo)
  {
    $anchors = $this->getAnchors($api);
    if (!empty($anchors)) {
      $this->printAnchorNavigation($anchors);
    } elseif ($api->isEditMode()) {
      $this->showNoAnchorsHint($api, $unit, $moduleInfo);
    }
  }

  /**
   * @param array $anchors
   */
  protected function printAnchorNavigation(array $anchors)
  {
    echo '<ul class="anchorList">';
    foreach ($anchors as $anchor) {
      $anchorLink = new HtmlTagBuilder('a', array(
        'class' => 'anchorLink',
        'href' => '#' . $anchor['id'],
      ), array($anchor['name']));

      $anchorItem = new HtmlTagBuilder('li', array(
        'class' => 'anchorItem'
      ), array($anchorLink));

      echo $anchorItem->toString();
    }
    echo '</ul>';
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI  $renderApi
   * @param \Render\Unit                  $unit
   * @param \Render\ModuleInfo            $moduleInfo
   */
  protected function showNoAnchorsHint($renderApi, $unit, $moduleInfo)
  {
    $i18n = new Translator($renderApi, $moduleInfo);
    $msg = $i18n->translate('hint.noAnchor');
    $errorTag = new HtmlTagBuilder('div', array(
      'class' => 'RUKZUKmissingInputHint'
    ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
    echo $errorTag->toString();
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   *
   * @return array
   */
  private function getAnchors($api)
  {
    $anchors = array();
    $allUnitData = $api->getAllUnitData();
    foreach ($allUnitData as $unitData) {
      if (isset($unitData['anchor'])) {
        if (isset($unitData['anchor']['id']) && isset($unitData['anchor']['name'])) {
          $anchors[] = $unitData['anchor'];
        }
      }
    }
    return $anchors;
  }
}
