<?php
namespace Rukzuk\Modules;

// ghost container aka page block area
class rz_ghost_container extends SimpleModule {

    public function renderContent($renderApi, $unit, $moduleInfo) {

        // find all children modules
        $allItems = $renderApi->getChildren($unit);
        $renderItems = array(); // normal units
        $nonRenderItems = array(); // extension units
        foreach ($allItems as $item) {
            if ($renderApi->getModuleInfo($item)->isExtension()) {
                // assume that extension modules (i.e. styles) render no html output
                $nonRenderItems[] = $item;
            } else {
                $renderItems[] = $item;
            }
        }

        // wrap all children
        if (empty($renderItems)) {
            if ($renderApi->isEditMode() && $renderApi->isTemplate()) {
                $i18n = new Translator($renderApi, $moduleInfo);
                $msg = $i18n->translate('msg.emptyInEditMode');
                $errorTag = new HtmlTagBuilder('div', array(
                    'class' => 'RUKZUKmissingInputHint'
                ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
                echo $errorTag->toString();
            }
        } else {
            $wrapTag = new HtmlTagBuilder('div');
            foreach ($renderItems as $renderItem) {
                echo $wrapTag->getOpenString();
                $renderApi->renderUnit($renderItem);
                echo $wrapTag->getCloseString();
            }
        }
    }
}
