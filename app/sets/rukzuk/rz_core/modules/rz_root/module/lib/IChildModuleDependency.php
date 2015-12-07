<?php
namespace Rukzuk\Modules;
/**
 */
interface IChildModuleDependency {

	/**
	 * Traversing up the tree until it finds the first
	 * parent occurence of a module by the given module id
	 *
	 * @param RenderAPI $renderApi
	 * @param Unit $unit
	 * @param string $parentModuleId
	 * @return boolean true if found else false
	 */
	public function isInsideModule( $renderApi, $unit, $parentModuleId );

} 
