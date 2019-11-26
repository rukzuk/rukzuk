<?php

require_once(MODULE_PATH.'/rz_form/module/lib/gui/ElementProperties.php');

use PHPUnit\Framework\TestCase;

class ElementPropertiesTest extends TestCase {

	/**
	 * @var IElementProperties
	 */
	private $object = null;

	public function setUp() : void {
		$this->object = new ElementProperties();
	}

	public function tearDown() : void {}

	/**
	 * @covers ElementProperties::addClass
	 * @covers ElementProperties::render
	 */
	public function testRender(){
		$this->object->addClass("test_style_class");
		$result = $this->object->render();
		$this->assertEquals(' class="test_style_class"', $result);
	}

}
 
