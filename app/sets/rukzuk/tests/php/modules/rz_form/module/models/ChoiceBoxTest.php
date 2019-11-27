<?php
require_once(MODULE_PATH.'/rz_form/module/models/ChoiceBox.php');

use \Test\Rukzuk\ModuleTestCase;


class ChoiceBoxTest extends ModuleTestCase {

	/**
	 * @var ChoiceBox
	 */
	private $object = null;

	private $unit = null;

	private $renderAPI = null;

	private $postValues = null;

	public function setUp() : void {
		$this->object = new ChoiceBox();

		$this->renderAPI = $this->createRenderApi(
			array(
				'mode' => 'edit'
			)
		);

		$this->postValues['fieldMUNIT-2222222-22222-22222222-MUNITfield'] = array('abc2_1','abc2_2*');
	}

	public function tearDown() : void {}

	/**
	 * @covers ChoiceBox::getRadioCheckbox
	 */
	public function testGetRadioButtons(){
		$this->setUnit('checkbox');
		$this->object->setListOptions($this->getListOptionsMock());
		$result = $this->object->getRadioCheckbox( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('Fieldset', $result);
		$this->assertEquals('<fieldset ><label  for="fieldMUNIT-2222222-22222-22222222-MUNITfield_0"><input  type="checkbox" value="abc2_1" name="fieldMUNIT-2222222-22222-22222222-MUNITfield[]" id="fieldMUNIT-2222222-22222-22222222-MUNITfield_0"/><span >abc2_1</span></label><label  for="fieldMUNIT-2222222-22222-22222222-MUNITfield_1"><input  type="checkbox" value="abc2_2" name="fieldMUNIT-2222222-22222-22222222-MUNITfield[]" id="fieldMUNIT-2222222-22222-22222222-MUNITfield_1" checked/><span >abc2_2</span></label></fieldset>', $result->renderElement());
	}

	/**
	 * @covers ChoiceBox::getRadioCheckbox
	 */
	public function testGetEmptyRadioButtons(){
		$this->setUnit('checkbox');
		$this->object->setListOptions($this->getEmptyListOptionsMock());
		$result = $this->object->getRadioCheckbox( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('Fieldset', $result);
		$this->assertEquals('<fieldset ></fieldset>', $result->renderElement());
	}

	/**
	 * @covers ChoiceBox::getRadioCheckbox
	 */
	public function testGetCheckboxes(){
		$this->setUnit('radio');
		$this->object->setListOptions($this->getListOptionsMock());
		$result = $this->object->getRadioCheckbox( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('Fieldset', $result);
		$this->assertEquals('<fieldset ><label  for="fieldMUNIT-2222222-22222-22222222-MUNITfield_0"><input  type="radio" value="abc2_1" name="fieldMUNIT-2222222-22222-22222222-MUNITfield[]" id="fieldMUNIT-2222222-22222-22222222-MUNITfield_0"/><span >abc2_1</span></label><label  for="fieldMUNIT-2222222-22222-22222222-MUNITfield_1"><input  type="radio" value="abc2_2" name="fieldMUNIT-2222222-22222-22222222-MUNITfield[]" id="fieldMUNIT-2222222-22222-22222222-MUNITfield_1" checked/><span >abc2_2</span></label></fieldset>', $result->renderElement());
	}

	/**
	 * @covers ChoiceBox::getRadioCheckbox
	 */
	public function testGetEmptyCheckboxes(){
		$this->setUnit('radio');
		$this->object->setListOptions($this->getEmptyListOptionsMock());
		$result = $this->object->getRadioCheckbox( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('Fieldset', $result);
		$this->assertEquals('<fieldset ></fieldset>', $result->renderElement());
	}

	/**
	 * @covers ChoiceBox::getSelectField
	 */
	public function testGetSelectField(){
		$this->setUnit('select');
		$this->object->setListOptions($this->getListOptionsMock());
		$result = $this->object->getSelectField( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('SelectField', $result);
		$this->assertEquals('<select  name="fieldMUNIT-2222222-22222-22222222-MUNITfield" id="fieldMUNIT-2222222-22222-22222222-MUNITfield"><option  value="abc2_1">abc2_1</option><option  value="abc2_2" selected>abc2_2</option></select>', $result->renderElement());
	}

	/**
	 * @covers ChoiceBox::getSelectField
	 */
	public function testGetEmptySelectField(){
		$this->setUnit('select');
		$this->object->setListOptions($this->getEmptyListOptionsMock());
		$result = $this->object->getSelectField( $this->renderAPI, $this->unit, 'fieldMUNIT-2222222-22222-22222222-MUNITfield' );

		$this->assertInstanceOf('SelectField', $result);
		$this->assertEquals('<select  name="fieldMUNIT-2222222-22222-22222222-MUNITfield" id="fieldMUNIT-2222222-22222-22222222-MUNITfield"></select>', $result->renderElement());
	}

	private function setUnit( $listType ){
		$this->unit = $this->createUnit(
			array(
				'id' => 'test-choicebox',
				'formValues' => array('listType' => $listType)
			)
		);
	}

	/**
	 * @return IListOptions
	 */
	private function getListOptionsMock(){
		$options1Mock = $this->getMock('IOption');
		$options1Mock->expects($this->any())
			->method('getValue')
			->will($this->returnValue('abc2_1'));
		$options1Mock->expects($this->any())
			->method('getName')
			->will($this->returnValue('abc2_1'));
		$options1Mock->expects($this->any())
			->method('isChecked')
			->will($this->returnValue(false));

		$options2Mock = $this->getMock('IOption');
		$options2Mock->expects($this->any())
			->method('getValue')
			->will($this->returnValue('abc2_2'));
		$options2Mock->expects($this->any())
			->method('getName')
			->will($this->returnValue('abc2_2'));
		$options2Mock->expects($this->any())
			->method('isChecked')
			->will($this->returnValue(true));

		$optionProviderMock = $this->getMock('IOptionProvider');
		$optionProviderMock->expects($this->any())
			->method('getOptions')
			->will($this->returnValue(array($options1Mock, $options2Mock)));
		$optionProviderMock->expects($this->any())
			->method('hasOptions')
			->will($this->returnValue(true));

		$listOptionsMock = $this->getMock('IListOptions');
		$listOptionsMock->expects($this->any())
			->method('getListOptions')
			->will($this->returnValue($optionProviderMock));

		return $listOptionsMock;
	}

	/**
	 * @return IListOptions
	 */
	private function getEmptyListOptionsMock(){
		$optionProviderMock = $this->getMock('IOptionProvider');
		$optionProviderMock->expects($this->any())
			->method('hasOptions')
			->will($this->returnValue(false));

		$listOptionsMock = $this->getMock('IListOptions');
		$listOptionsMock->expects($this->any())
			->method('getListOptions')
			->will($this->returnValue($optionProviderMock));

		return $listOptionsMock;
	}
}
 
