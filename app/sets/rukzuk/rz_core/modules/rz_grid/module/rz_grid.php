<?php
namespace Rukzuk\Modules;

class rz_grid extends SimpleModule {

    protected function modifyWrapperTag($tag, $renderApi, $unit, $moduleInfo) {
        // show add module button in page mode if this is a ghost container
        if ($renderApi->isEditMode() && $renderApi->isPage() && $unit->isGhostContainer()) {
            $tag->addClass('showAddModuleButton');
        }
    }

    protected function renderContent($renderApi, $unit, $moduleInfo) {

        // wrapping needed; otherwise gridRaster isn't correct when using rz_style_padding_margin (padding)
        $gridElementsTag = new HtmlTagBuilder('div', array(
            'class' => 'gridElements'
        ));
        echo $gridElementsTag->getOpenString();

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

        echo $gridElementsTag->getCloseString();

        // needed for vertical alignment when grid is higher than grid elements
        $fillHeightTag = new HtmlTagBuilder('div', array(
            'class' => 'fillHeight'
        ));
        echo $fillHeightTag->toString();
    }

}
