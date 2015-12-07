<?php
namespace Rukzuk\Modules;

require_once(dirname(__FILE__) . '/ICacheBuster.php');


class CacheBuster implements ICacheBuster{

	/**
	 * @var array
	 */
	private $moduleManifest = null;

	public function suffix($file){
		return (isset($this->moduleManifest['version']))?$file.'?'.$this->moduleManifest['version']:'';
	}

	/**
	 * @param array $moduleManifest
	 */
	public function setModuleManifest( array $moduleManifest ) {
		$this->moduleManifest = $moduleManifest;
	}

} 
