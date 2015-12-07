<?php

namespace Rukzuk\Modules;

require_once( dirname( __FILE__ ) . "/IChildModuleDependency.php" );

/**
 */
class ChildModuleDependency implements IChildModuleDependency{

	public function isInsideModule( $renderApi, $unit, $parentModuleId ) {
		$formUnit = $renderApi->getParentUnit($unit);
		while ( isset( $formUnit ) && $renderApi->getModuleInfo( $formUnit )->getId() !== $parentModuleId ) {
			$formUnit = $renderApi->getParentUnit( $formUnit );
		}
		return isset($formUnit);
	}

	public function isInsideUnit( $renderApi, $unit, $parentUnitId ) {
		if( $parentUnitId === $unit->getId() ){
			$formUnit = $unit;
		}else {
			$formUnit = $renderApi->getParentUnit( $unit );
			while( isset( $formUnit ) && $formUnit->getId() !== $parentUnitId ) {
				$formUnit = $renderApi->getParentUnit( $formUnit );
			}
		}
		return isset($formUnit);
	}
}
