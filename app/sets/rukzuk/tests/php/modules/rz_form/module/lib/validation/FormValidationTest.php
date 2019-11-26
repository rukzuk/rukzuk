<?php
require_once(MODULE_PATH.'/rz_form/module/lib/validation/FormValidation.php');

use PHPUnit\Framework\TestCase;

class FormValidationTest extends TestCase {

	/**
	 * @var IFormValidation
	 */
	private $object = null;

	public function setUp() : void {
		$this->object = new FormValidation();
	}

	public function tearDown() : void {}

	/**
	 * @covers FormValidation::isValidEmailAddress
	 */
	public function testIsValidEmailAddress(){
		$result = $this->object->isValidEmailAddress('chris@rukzuk.com');
		$this->assertTrue($result);
	}

	/**
	 * @covers FormValidation::isValidEmailAddress
	 */
	public function testIsNotValidEmailAddress(){
		$result = $this->object->isValidEmailAddress('chris@rukzukcom');
		$this->assertFalse($result);
	}

	/**
	 * @covers FormValidation::isNumeric
	 */
	public function testIsNumeric(){
		$result = $this->object->isNumeric(123);
		$this->assertTrue($result);
	}

	/**
	 * @covers FormValidation::isNumeric
	 */
	public function testIsNotNumeric(){
		$result = $this->object->isNumeric('abc');
		$this->assertFalse($result);
	}

	/**
	 * @covers FormValidation::isFilled
	 */
	public function testIsFilled(){
		$result = $this->object->isFilled('abc');
		$this->assertTrue($result);
	}

	/**
	 * @covers FormValidation::isFilled
	 */
	public function testIsNotFilled(){
		$result = $this->object->isFilled('');
		$this->assertFalse($result);
	}
}
 
