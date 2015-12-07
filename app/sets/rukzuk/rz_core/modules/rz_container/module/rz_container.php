<?php
namespace Rukzuk\Modules;

class rz_container extends SimpleModule {

    protected function renderContent($renderApi, $unit, $moduleInfo) {

        $elementsTag = new HtmlTagBuilder('div', array(
            'class' => 'cntElements'
        ));
        echo $elementsTag->getOpenString();

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
        $wrapTag = new HtmlTagBuilder('div');
        for ($i = 0; $i < count($renderItems); $i++) {
            echo $wrapTag->getOpenString();
            $renderApi->renderUnit($renderItems[$i]);
            echo $wrapTag->getCloseString();
        }

        echo $elementsTag->getCloseString();

        // needed for vertical alignment when container is higher than contained children
        $fillHeightTag = new HtmlTagBuilder('div', array(
            'class' => 'fillHeight'
        ));
        echo $fillHeightTag->toString();
    }

}
