<?php
namespace Rukzuk\Modules;

class rz_trigger_event extends SimpleModule {

    /**
     * @param \Render\APIs\APIv1\CSSAPI $api
     * @param \Render\ModuleInfo $moduleInfo
     * @return string
     */
    protected function htmlHead($api, $moduleInfo) {
        return "<script>window.rz_trigger_event = [];</script>";
    }

    public function htmlHeadUnit($api, $unit, $moduleInfo) {

        if ($api->isEditMode() && $api->getFormValue($unit, 'displayInEditmode')) {
          return;
        }

        // enable event only if this extension unit is a direct child of default unit
        $parentUnit = $api->getParentUnit($unit);
        if (!$api->getModuleInfo($parentUnit)->isExtension()) {
            $eventType = $api->getFormValue($unit, 'eventType');
            $eventLimit = $api->getFormValue($unit, 'eventLimit');
            $eventMode = $api->getFormValue($unit, 'eventMode');



            $stateName = '';
            if ($api->getFormValue($unit, 'enableState')) {
                $stateName = $api->getFormValue($unit, 'stateName');
            }
            $selector = substr($api->getFormValue($unit, 'additionalSelector'), 2);
            $code = "window.rz_trigger_event.push({ ";
            $code .= "\"selector\": \"" . $selector ."\", \"parentUnitId\": \"" . $parentUnit->getId() ."\", \"eventType\": \"" . $eventType ."\", \"stateName\": \"" . $stateName ."\", \"eventLimit\": \"" . $eventLimit ."\", \"eventMode\": \"" . $eventMode ."\"";
            if ($eventType == 'scroll') {
                $code .= ",\"scrollConfig\": \"" . $api->getFormValue($unit, 'scrollConfig') ."\"";
            }
            $code .= "});";
            return "<script>".$code."</script>";
        } else {
            if ($api->isEditMode()) {
                $i18n = new Translator($api, $moduleInfo);
                $msg = $i18n->translate('error.insideExtensionModule');
                $code = 'alert("' . addslashes($msg) . '");';
            }
            return "<script>".$code."</script>";
        }

    }

    /**
     * Allow loading of require modules in live mode
     * @param \Render\APIs\APIv1\HeadAPI $api
     * @param \Render\ModuleInfo $moduleInfo
     * @return array
     */
    protected function getJsModulePaths($api, $moduleInfo) {
        $paths = parent::getJsModulePaths($api, $moduleInfo);
        if (is_null($paths)) {
            $paths = array();
        }
        $paths[$moduleInfo->getId()] = $moduleInfo->getAssetUrl();
        return $paths;
    }

}
