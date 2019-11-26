<?php

require_once(MODULE_PATH.'/rz_form/module/lib/option_provider/OptionProvider.php');

use PHPUnit\Framework\TestCase;

class OptionProviderTest extends TestCase {

	/**
	 * @var IOptionProvider
	 */
	private $object = null;

	public function setUp() : void {
		$this->object = new OptionProvider();
	}

	public function tearDown() : void {}

	/**
	 * @covers OptionProvider::hasOptions
	 */
	public function testHasNoOptions(){
		$result = $this->object->hasOptions();
		$this->assertFalse($result);
	}

	/**
	 * @covers OptionProvider::addOption
	 * @covers OptionProvider::getOptions
	 */
	public function testGetOptions(){
		$this->applyOptions();
		$result = $this->object->getOptions();
		$this->assertCount(3, $result);
	}

	/**
	 * @covers OptionProvider::addOption
	 * @covers OptionProvider::getOptions
	 */
	public function testGetOptionsHasInstance(){
		$this->applyOptions();
		$result = $this->object->getOptions();
		$this->assertInstanceOf('IOption', $result[0]);
	}

	/**
	 * @covers OptionProvider::addOption
	 * @covers OptionProvider::hasOptions
	 */
	public function testHasOptions(){
		$this->applyOptions();
		$result = $this->object->hasOptions();
		$this->assertTrue($result);
	}

	private function applyOptions(){
		$option1 = $this->getMock('IOption');
		$option2 = $this->getMock('IOption');
		$option3 = $this->getMock('IOption');
		$this->object->addOption($option1);
		$this->object->addOption($option2);
		$this->object->addOption($option3);
	}

}
 
