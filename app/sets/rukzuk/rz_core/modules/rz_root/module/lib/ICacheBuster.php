<?php
namespace Rukzuk\Modules;


interface ICacheBuster {

	/**
	 * Add query string suffix to the given filename
	 * to pretend to be a new file for bypassing the
	 * browser cache.
	 *
	 * @param string $file
	 * @return string
	 */
	public function suffix($file);

	/**
	 * Set the module manifest
	 *
	 * @param array $moduleManifest
	 */
	public function setModuleManifest( array $moduleManifest );
} 
