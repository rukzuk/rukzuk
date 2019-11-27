<?php

require_once(MODULE_PATH.'/rz_form/module/lib/http/Request.php');

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase {

	/**
	 * @var IFormRequest
	 */
	private $object = null;

	public function setUp() : void {
		$this->object = new Request();
		$this->object->setPostValues($this->getPreparedPostValues());
	}

	public function tearDown() : void {}

	/**
	 * @covers Request::isPostRequest
	 */
	public function testIsNotPostRequest(){
		$result = $this->object->isPostRequest();
		$this->assertFalse($result);
	}

	/**
	 * @covers Request::getPostValues
	 */
	public function testGetPostValuesCount(){
		$result = $this->object->getPostValues();
		$this->assertCount(3, $result);
	}

	/**
	 * @covers Request::getPostValues
	 */
	public function testCheckKeyReplacement(){
		$result = $this->object->getPostValues();
		$this->assertArrayHasKey("MUNIT-1111111-11111-11111111-MUNIT", $result);
	}

	/**
	 * @covers Request::getPostValues
	 */
	public function testCheckStringSanitize(){
		$result = $this->object->getPostValues();
		$this->assertStringEndsNotWith('<a href="#"></a>', $result["MUNIT-1111111-11111-11111111-MUNIT"]);
	}

	private function getPreparedPostValues(){
		$postValues = array();
		$postValues['fieldMUNIT-1111111-11111-11111111-MUNITfield'] = 'abc1';
		$postValues['fieldMUNIT-2222222-22222-22222222-MUNITfield'] = array('abc2_1','abc2_2');
		$postValues['fieldMUNIT-3333333-33333-33333333-MUNITfield'] = 'abc3<a href="#"></a>';
		return $postValues;
	}

}
 
