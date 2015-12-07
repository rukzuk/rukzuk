<?php

require_once(MODULE_PATH.'/rz_form/module/lib/gui/AbstractComponent.php');



class AbstractComponentTest extends PHPUnit_Framework_TestCase {

	public function setUp(){}

	public function tearDown(){}

	/**
	 * @covers AbstractComponent::renderElement
	 */
	public function testRenderVoidElementWithoutProperties(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			 ->method('getElementTag')
			 ->will($this->returnValue('input'));

		$elementPropertiesMock = $this->getMock('IElementProperties');
		$elementPropertiesMock->expects($this->any())
			->method('render')
			->will($this->returnValue(''));

		$stub->expects($this->any())
			 ->method('getElementProperties')
			 ->will($this->returnValue($elementPropertiesMock));

		$this->assertEquals('<input />', $stub->renderElement());
	}

	/**
	 * @covers AbstractComponent::renderElement
	 */
	public function testRenderVoidElementWithProperties(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			 ->method('getElementTag')
			 ->will($this->returnValue('input'));

		$elementPropertiesMock = $this->getMock( 'IElementProperties' );
		$elementPropertiesMock->expects( $this->any() )
			->method( 'render' )
			->will( $this->returnValue( 'class="i_got_mocked"' ) );

		$stub->expects($this->any())
			 ->method('getElementProperties')
			 ->will($this->returnValue($elementPropertiesMock));

		$this->assertEquals('<input class="i_got_mocked"/>', $stub->renderElement());
	}

	/**
	 * @covers AbstractComponent::renderElement
	 */
	public function testRenderElementWithoutProperties(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			->method('getElementTag')
			->will($this->returnValue('span'));

		$elementPropertiesMock = $this->getMock('IElementProperties');
		$elementPropertiesMock->expects($this->any())
			->method('render')
			->will($this->returnValue(''));

		$stub->expects($this->any())
			->method('getElementProperties')
			->will($this->returnValue($elementPropertiesMock));

		$this->assertEquals('<span ></span>', $stub->renderElement());
	}

	/**
	 * @covers AbstractComponent::renderElement
	 */
	public function testRenderElementWithProperties(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			->method('getElementTag')
			->will($this->returnValue('span'));

		$elementPropertiesMock = $this->getMock( 'IElementProperties' );
		$elementPropertiesMock->expects( $this->any() )
			->method( 'render' )
			->will( $this->returnValue( 'class="i_got_mocked"' ) );

		$stub->expects($this->any())
			->method('getElementProperties')
			->will($this->returnValue($elementPropertiesMock));

		$this->assertEquals('<span class="i_got_mocked"></span>', $stub->renderElement());
	}

	/**
	 * @covers AbstractComponent::isVoidElement
	 */
	public function testIsVoidTag(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			 ->method('getElementTag')
			 ->will($this->returnValue('input'));

		$this->assertTrue($stub->isVoidElement());
	}

	/**
	 * @covers AbstractComponent::isVoidElement
	 */
	public function testIsNotVoidTag(){
		$stub = $this->getMockForAbstractClass('AbstractComponent');
		$stub->expects($this->any())
			->method('getElementTag')
			->will($this->returnValue('span'));

		$this->assertFalse($stub->isVoidElement());
	}

}
 