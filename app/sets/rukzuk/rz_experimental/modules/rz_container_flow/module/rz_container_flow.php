<?php
namespace Rukzuk\Modules;

class rz_container_flow extends SimpleModule {

    /**
     * @param \Render\APIs\APIv1\RenderAPI $renderApi
     * @param \Render\Unit $unit
     * @param \Render\ModuleInfo $moduleInfo
     */
    protected function renderContent($renderApi, $unit, $moduleInfo) {
        $renderApi->renderChildren($unit);
    }
}
