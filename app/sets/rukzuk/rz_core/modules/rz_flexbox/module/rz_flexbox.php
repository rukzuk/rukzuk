<?php
namespace Rukzuk\Modules;

class rz_flexbox extends SimpleModule
{

    protected function renderContent($renderApi, $unit, $moduleInfo) {

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
		echo $wrapTag->getOpenString();
        for ($i = 0; $i < count($renderItems); $i++) {
            $renderApi->renderUnit($renderItems[$i]);
        }
		echo $wrapTag->getCloseString();

    }
}
