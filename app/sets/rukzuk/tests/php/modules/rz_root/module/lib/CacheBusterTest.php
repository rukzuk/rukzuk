<?php
require_once(MODULE_PATH.'/rz_root/module/lib/CacheBuster.php');


class CacheBusterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var \Rukzuk\Modules\ICacheBuster
	 */
	private $object = null;

	public function setUp() {
		$this->object = new \Rukzuk\Modules\CacheBuster();
	}

	public function tearDown() {
	}

//	public function testSuffix() {
//		$fileName = "module_javascript.js";
//		$result = $this->object->suffix( $fileName );
//
//		// module_javascript.js?4.0.2
//		$this->assertRegExp( '/\d{1,1}+(\.\d+){2,2}+$/', $result );
//		//						   ^	  ^	    ^   ^-- must occure at the end of the string
//		//						   '	  '	    '-- there must be 2 repetitions of the version number and dot
//	    //						   '	  '-- matches '.0.2'
//		//						   '-- there must be 1 occurance of the first digit
//		//
//		// any improvement to the above regex are more then welcome
//	}

	public function testSuffix() {
		$fileName = "module_javascript.js";
		$this->object->setModuleManifest(array('version' => 'dev'));
		$result = $this->object->suffix( $fileName );
		$this->assertContains('?', $result);
	}

}
 
