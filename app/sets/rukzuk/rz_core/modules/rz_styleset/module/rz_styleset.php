<?php
namespace Rukzuk\Modules;

class rz_styleset extends SimpleModule {

  public function render($renderApi, $unit, $moduleInfo)
  {

    if (!$renderApi->isEditMode()) {
      return;
    }

    $tag = new HtmlTagBuilder('div');
    $tag->set('id', $unit->getId());
    $tag->set('data-styleset', $renderApi->getFormValue($unit, 'cssStyleSet'));
    $tag->set('data-stylesetname', $renderApi->getFormValue($unit, 'cssStyleSetName'));
    $tag->addClass($moduleInfo->getId());
    $tag->addClass('isModule');
    $tag->addClass($unit->getHtmlClass());

    // call hook
    $this->modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo);

    echo $tag->getOpenString();
    $this->renderContent($renderApi, $unit, $moduleInfo);
    echo $tag->getCloseString();
  }

  /**
   * @param $renderApi
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function renderContent($renderApi, $unit, $moduleInfo)
  {
    $renderApi->renderChildren($unit);
  }


  /**
   * Build the DynCSS config array for this unit
   * @param \Render\APIs\APIv1\HeadAPI $api
   * @param \Render\Unit $unit
   * @param \Render\ModuleInfo $moduleInfo
   * @return array
   */
  protected function getDynCSSConfig($api, $unit, $moduleInfo)
  {
    // performance improvement as we cache the
    // css in live mode so skip gathering of data
    if ($api->isLiveMode()) {
      return array();
    }

    $selector = $api->getFormValue($unit, 'cssStyleSet');
    if ($selector != '') {
      $result['selector'] = array('.' . $selector);
    }

    return $result;
  }

}
