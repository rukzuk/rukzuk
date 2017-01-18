<?php
namespace Rukzuk\Modules;

class rz_page_link extends SimpleModule {

    public function renderContent($renderApi, $unit, $moduleInfo) {

        // show hint when no children inserted
        if ($renderApi->isEditMode() && count($renderApi->getChildren($unit)) === 0) {
            $i18n = new Translator($renderApi, $moduleInfo);
            $msg = $i18n->translate('error.pleaseInsertModules');
            $errorTag = new HtmlTagBuilder('div', array(
                'class' => 'RUKZUKmissingInputHint'
            ), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
            echo $errorTag->toString();
        } else {
            $pageId = $renderApi->getFormValue($unit, 'teaserPageId');
            global $currentTeaserPageId;
            $currentTeaserPageId = $pageId;
            $renderApi->renderChildren($unit);
        }
    }
}