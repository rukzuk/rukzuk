<?php


namespace Render;


use Render\Unit;
use Test\Rukzuk\AbstractTestCase;

class UnitTest extends  AbstractTestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_createNewInstance()
  {
    // ARRANGE
    $expectedId = 'this is the unit id';
    $expectedModuleId = 'this is the unit module id';
    $expectedName = 'this is the unit name';
    $expectedFormValues = array('this is one unit form value');
    $expectedGhostContainer = true;
    $expectedTemplateUnitId = 'this is the unit template id';
    $expectedHtmlClass = 'this is the html class';

    // ACT
    $actualUnit = new Unit($expectedId, $expectedModuleId, $expectedName,
      $expectedFormValues, $expectedGhostContainer, $expectedTemplateUnitId, $expectedHtmlClass);

    // ASSERT
    $this->assertEquals($expectedId, $actualUnit->getId());
    $this->assertEquals($expectedModuleId, $actualUnit->getModuleId());
    $this->assertEquals($expectedName, $actualUnit->getName());
    $this->assertEquals($expectedFormValues, $actualUnit->getFormValues());
    $this->assertEquals($expectedGhostContainer, $actualUnit->isGhostContainer());
    $this->assertEquals($expectedTemplateUnitId, $actualUnit->getTemplateUnitId());
    $this->assertEquals($expectedHtmlClass, $actualUnit->getHtmlClass());
  }
}
 