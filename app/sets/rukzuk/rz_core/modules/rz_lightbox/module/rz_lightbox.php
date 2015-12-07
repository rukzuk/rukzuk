<?php
namespace Rukzuk\Modules;

use Render\APIs\APIv1\RenderAPI;
use Render\ModuleInfo;
use Render\Unit;

require_once( dirname( __FILE__ ) . "/models/LightboxModuleDependency.php" );

/**
 * @package      Rukzuk\Modules\rz_lightbox
 */
class rz_lightbox extends SimpleModule {

	public function renderContent( $renderApi, $unit, $moduleInfo ) {

		$moduleDependency = new \LightboxModuleDependency();

		if (!$moduleDependency->isInsideModule($renderApi, $unit, 'rz_lightbox')) {
			$renderApi->renderChildren($unit);
		} else {
			$i18n = new Translator($renderApi, $moduleInfo);
			$msg = $i18n->translate('error.noLightboxModuleInheritance');
			$errorTag = new HtmlTagBuilder('div', array(
				'class' => 'RUKZUKmissingInputHint'
			), array(new HtmlTagBuilder('button', array('style' => 'cursor: default;'), array($msg))));
			echo $errorTag->toString();
		}
	}

	/**
	 * Allow loading of require modules in live mode
	 * @param \Render\APIs\APIv1\HeadAPI $api
	 * @param \Render\ModuleInfo $moduleInfo
	 * @return array
	 */
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