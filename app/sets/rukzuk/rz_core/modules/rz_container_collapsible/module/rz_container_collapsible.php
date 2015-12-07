<?php
namespace Rukzuk\Modules;

class rz_container_collapsible extends SimpleModule {

    protected function modifyWrapperTag($tag, $renderApi /* TODO naming */, $unit, $moduleInfo)
    {
      if ($renderApi->getFormValue($unit, 'initialState') == 'collapsed') {
        $tag->addClass('collapsed');
      }
    }

    protected function renderContent($api, $unit, $moduleInfo)
    {
      $handlePos = $api->getFormValue($unit, 'handlePosition');
      $handle = $this->getHandleCode($api, $unit);

      if ($handlePos == 'top') {
        echo $handle;
      }

      $contentTag = new HtmlTagBuilder('div', array(
        'class' => 'collapsibleContent'
      ));

      echo $contentTag->getOpenString();
      $api->renderChildren($unit);
      $this->insertMissingInputHint($api, $unit); /* TODO only working in page mode? */
      echo $contentTag->getCloseString();

      if ($handlePos == 'bottom') {
        echo $handle;
      }
    }

    protected function getHandleCode($api, $unit)
    {
      $textExpand = $api->getFormValue($unit, 'handleTextExpand');
      $textCollapse = $api->getFormValue($unit, 'handleTextCollapse');
      if (empty($textCollapse)) {
          $textCollapse = $textExpand;
      }

      $handle = new HtmlTagBuilder('div', array(
        'class' => 'collapsibleHandle',
        'data-duration' => str_replace('ms', '', $api->getFormValue($unit, 'animationDuration')),
        'data-closeonlinkclick' => $api->getFormValue($unit, 'enableCloseOnLinkClick')
      ), array(
          new HtmlTagBuilder('div', array('class' => 'collapse'), array(new HtmlTagBuilder('span', null, array($textCollapse) /* TODO why array needed? */))),
          new HtmlTagBuilder('div', array('class' => 'expand'), array(new HtmlTagBuilder('span', null, array($textExpand))))
      ));

      if ($api->getFormValue($unit, 'initialState') == 'collapsed') {
        $handle->addClass('collapsed');
      }

      return $handle->toString();
    }


    /**
     * Allow loading of require modules in live mode
     * @param \Render\APIs\APIv1\HeadAPI $api
     * @param \Render\ModuleInfo $moduleInfo
     * @return array
     */
    /* TODO why is this needed? */
    protected function getJsModulePaths($api, $moduleInfo)
    {
      $paths = parent::getJsModulePaths($api, $moduleInfo);
      if (is_null($paths)) {
        $paths = array();
      }
      $paths[$moduleInfo->getId()] = $moduleInfo->getAssetUrl();
      return $paths;
    }

}