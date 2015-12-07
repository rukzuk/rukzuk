<?php
namespace Test\Rukzuk;

class RenderApiMockTest_Module extends \Rukzuk\Modules\SimpleModule
{
  public function provideUnitData($api, $unit, $moduleInfo)
  {
    return array(
      'unit' => $unit->getId(),
      'module' => $moduleInfo->getId()
    );
  }
}

class RenderApiMockTest extends ModuleTestCase
{
  public function testUnitTree_getUnitsById()
  {
    $api = new RenderApiMock(array(
      'units' => array(
        'u1' => array(
          'children' => array(
            'u11' => array(
              'children' => array('u111', 'u112', 'u113'),
            ),
            'u12' => array(
              'children' => array(
                'u121' => array(),
                'u122' => array(),
              )
            )
          )
        )
      )
    ));

    // execute
    $u1 = $api->getUnitById('u1');
    $u11 = $api->getUnitById('u11');
    $u111 = $api->getUnitById('u111');
    $u112 = $api->getUnitById('u112');
    $u113 = $api->getUnitById('u113');
    $u12 = $api->getUnitById('u12');
    $u121 = $api->getUnitById('u121');
    $u122 = $api->getUnitById('u122');

    // verify
    $this->assertEquals('u1', $u1->getId());
    $this->assertEquals('u11', $u11->getId());
    $this->assertEquals('u111', $u111->getId());
    $this->assertEquals('u112', $u112->getId());
    $this->assertEquals('u113', $u113->getId());
    $this->assertEquals('u12', $u12->getId());
    $this->assertEquals('u121', $u121->getId());
    $this->assertEquals('u122', $u122->getId());
  }

  public function testUnitTree_getParentNode()
  {
    $api = new RenderApiMock(array(
      'units' => array(
        'u1' => array(
          'children' => array(
            'u11' => array(
              'children' => array('u111')
            )
          )
        )
      )
    ));

    // execute
    $p1 = $api->getParentUnit($api->getUnitById('u1'));
    $p11 = $api->getParentUnit($api->getUnitById('u11'));
    $p111 = $api->getParentUnit($api->getUnitById('u111'));

    // verify
    $this->assertNull($p1);
    $this->assertEquals('u1', $p11->getId());
    $this->assertEquals('u11', $p111->getId());
  }

  public function testUnitTree_getChildren()
  {
    $api = new RenderApiMock(array(
      'units' => array(
        'u1' => array(
          'children' => array(
            'u11' => array(
              'children' => array('u111', 'u112')
            )
          )
        )
      )
    ));

    // execute
    $c1 = $api->getChildren($api->getUnitById('u1'));
    $c11 = $api->getChildren($api->getUnitById('u11'));

    // verify
    $this->assertEquals(1, count($c1));
    $c1_0 = $c1[0]; // bloody php
    $this->assertInstanceOf('\Render\Unit', $c1_0);
    $this->assertEquals('u11', $c1_0->getId());

    $this->assertEquals(2, count($c11));
    $c11_0 = $c11[0];
    $c11_1 = $c11[1];
    $this->assertInstanceOf('\Render\Unit', $c11_0);
    $this->assertInstanceOf('\Render\Unit', $c11_1);
    $this->assertEquals('u111', $c11_0->getId());
    $this->assertEquals('u112', $c11_1->getId());
  }

  public function testGetModuleInfo()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'units' => array('foo'),
      'modules' => array(
        'foo' => array(
          'assetPath' => 'foo-path',
          'assetUrl' => 'foo-url'
        )
      )
    ));
    // execute
    $modulInfo = $api->getModuleInfo($api->getUnitById('foo'));
    // verify
    $this->assertEquals('foo-path', $modulInfo->getAssetPath());
    $this->assertEquals('foo-url', $modulInfo->getAssetUrl());
  }

  public function testGetAllUnitData()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'units' => array(
        'foo' => array(
          'moduleId' => 'testModule1',
          'children' => array(
            'bar' => array(
              'moduleId' => 'testModule1'
            ),
            'baz' => array(
              'moduleId' => 'testModule2'
            )
          )
        )
      ),
      'modules' => array(
        'testModule1' => array(
          'ns' => '\Test\Rukzuk',
          'class' => 'RenderApiMockTest_Module'
        ),
        'testModule2' => array(
          'ns' => '\Test\Rukzuk',
          'class' => 'RenderApiMockTest_Module'
        )
      )
    ));
    // execute
    $unitData = $api->getAllUnitData();
    // verify
    $this->assertEquals(array(
      'foo' => array('unit' => 'foo', 'module' => 'testModule1'),
      'bar' => array('unit' => 'bar', 'module' => 'testModule1'),
      'baz' => array('unit' => 'baz', 'module' => 'testModule2')
    ), $unitData);
  }
}

