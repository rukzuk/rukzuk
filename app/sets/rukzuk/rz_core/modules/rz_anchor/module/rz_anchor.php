<?php
namespace Rukzuk\Modules;

class rz_anchor extends SimpleModule
{
  /**
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit              $unit
   * @param \Render\ModuleInfo        $moduleInfo
   *
   * @return array|void
   */
  public function provideUnitData($api, $unit, $moduleInfo)
  {
    $unitData = parent::provideUnitData($api, $unit, $moduleInfo);
    $unitData['anchor'] = $this->getAnchor($api, $unit);
    return $unitData;
  }

  /**
   * @param HtmlTagBuilder               $tag
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  protected function modifyWrapperTag($tag, $api, $unit, $moduleInfo)
  {
    if (count($api->getChildren($unit)) === 0) {
      $tag->addClass('isAnchorAbsolute');
    }
  }

  /**
   * @param \Render\APIs\APIv1\RenderAPI $api
   * @param \Render\Unit                 $unit
   * @param \Render\ModuleInfo           $moduleInfo
   */
  protected function renderContent($api, $unit, $moduleInfo)
  {
    $anchor = $this->getAnchor($api, $unit);

    echo '<div id="' . $anchor['id'] . '" class="anchor">';
    if ($api->isEditMode()) {
      echo '<div>#' . $anchor['id'] . '</div>';
    }
    echo '</div>';

    $api->renderChildren($unit);
  }

  /**
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit              $unit
   *
   * @return array
   */
  protected function getAnchor($api, $unit)
  {
    $anchorName = $api->getFormValue($unit, 'anchorName', '');
    $anchorId = $api->getFormValue($unit, 'anchorId', '');
    $notInNavigation = $api->getFormValue($unit, 'notInNavigation', '');
    if (substr($anchorId, 0, 1) === '#') {
      $anchorId = substr($anchorId, 1);
    }
    return array(
      'name'  => $anchorName,
      'id'    => empty($anchorId) ? base64_encode($anchorName) : $anchorId,
      'notInNavigation' => $notInNavigation
    );
  }
}
