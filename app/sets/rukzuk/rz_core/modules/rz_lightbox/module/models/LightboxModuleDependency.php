<?php
require_once( dirname( __FILE__ ) . "/ILightboxModuleDependency.php" );

/**
 */
class LightboxModuleDependency implements ILightboxModuleDependency{

	public function isInsideModule( $renderApi, $unit, $parentModuleId ) {
		$formUnit = $renderApi->getParentUnit($unit);
		while ( isset( $formUnit ) && $renderApi->getModuleInfo( $formUnit )->getId() !== $parentModuleId ) {
			$formUnit = $renderApi->getParentUnit( $formUnit );
		}
		return isset($formUnit);
	}
}