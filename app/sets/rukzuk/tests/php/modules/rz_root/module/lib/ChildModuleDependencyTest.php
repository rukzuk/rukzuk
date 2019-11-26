<?php
use \Test\Rukzuk\ModuleTestCase;



class ChildModuleDependencyTest extends ModuleTestCase {

	/**
	 * @var \Rukzuk\Modules\ChildModuleDependency
	 */
	private $object = null;

	public function setUp() : void {
		$this->object = new \Rukzuk\Modules\ChildModuleDependency();
	}

	public function tearDown() : void {}

	public function testIsInsideModule(){
		$result = $this->object->isInsideModule( $this->getRenderApiMock("rz_form"), $this->getUnitMock('test_2_test'), "rz_form" );
		$this->assertTrue($result);
	}

	public function testIsInsideUnit(){
		$result = $this->object->isInsideModule( $this->getRenderApiMock("rz_form"), $this->getUnitMock('test_2_test'), "rz_form" );
		$this->assertTrue($result);
	}

	private function getRenderApiMock($parentModuleId){
		$renderMock = $this->getMock('RenderAPI', array('getParentUnit', 'getModuleInfo'));
		$renderMock->expects($this->any())
			->method('getParentUnit')
			->with($this->isInstanceOf($this->getUnitMock('test_2_test')))
			->will($this->returnValue($this->getUnitMock('test_1_test')));

		$renderMock->expects($this->any())
			->method('getModuleInfo')
			->with($this->isInstanceOf($this->getUnitMock('test_1_test')))
			->will($this->returnValue($this->getModuleInfoMock($parentModuleId)));

		return $renderMock;
	}

	private function getUnitMock($unitId){
		$unitMock = $this->getMock('Unit');
		$unitMock->expects($this->any())
			->method('getId')
			->will($this->returnValue($unitId));

		return $unitMock;
	}

	private function getModuleInfoMock($moduleId){
		$moduleInfoMock = $this->getMock('ModuleInfo', array('getId'));
		$moduleInfoMock->expects($this->any())
			->method('getId')
			->will($this->returnValue($moduleId));

		return $moduleInfoMock;
	}
}
 
