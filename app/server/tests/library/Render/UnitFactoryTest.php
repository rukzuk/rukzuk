<?php


namespace Render;


use Render\UnitFactory;
use Test\Rukzuk\AbstractTestCase;

class UnitFactoryTest extends  AbstractTestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_contentToUnit_success()
  {
    // ARRANGE
    $unitFactory = new UnitFactory();
    $content = array(
      'id' => 'UNIT-00000000-0000-0000-0000-000000000000-UNIT',
      'moduleId' => 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL',
      'name' => 'SimpleTestUnit Root Unit',
    );
    $defaultFromValues = array();
    $expectedFormValues = array();

    // ACT
    $actualUnit = $unitFactory->contentToUnit($content, $defaultFromValues);

    // ASSERT
    $this->assertEquals($content['id'], $actualUnit->getId());
    $this->assertEquals($content['moduleId'], $actualUnit->getModuleId());
    $this->assertEquals($content['name'], $actualUnit->getName());
    $this->assertEquals('', $actualUnit->getHtmlClass());
    $this->assertEquals($expectedFormValues, $actualUnit->getFormValues());
    $this->assertFalse($actualUnit->isGhostContainer());
    $this->assertNull($actualUnit->getTemplateUnitId());
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_contentToUnit_return_expected_unit_from_page_content()
  {
    // ARRANGE
    $unitFactory = new UnitFactory();
    $content = array(
      'id' => 'UNIT-00000000-0000-0000-0000-000000000000-UNIT',
      'moduleId' => 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL',
      'name' => 'SimpleTestUnit Root Unit',
      'htmlClass' => 'this is the html class',
      'templateUnitId' => 'UNIT-00000000-0000-0000-0000-000000000001-UNIT',
      'ghostContainer' => true,
      'formValues' => array(
        'foo' => 'bar',
      ),
    );
    $defaultFromValues = array(
      'foo' => 'foo',
      'bar' => 'foo',
    );
    $expectedFormValues = array(
      'foo' => 'bar',
      'bar' => 'foo',
    );

    // ACT
    $actualUnit = $unitFactory->contentToUnit($content, $defaultFromValues);

    // ASSERT
    $this->assertEquals($content['id'], $actualUnit->getId());
    $this->assertEquals($content['moduleId'], $actualUnit->getModuleId());
    $this->assertEquals($content['name'], $actualUnit->getName());
    $this->assertEquals($content['htmlClass'], $actualUnit->getHtmlClass());
    $this->assertEquals($expectedFormValues, $actualUnit->getFormValues());
    $this->assertTrue($actualUnit->isGhostContainer());
    $this->assertEquals($content['templateUnitId'], $actualUnit->getTemplateUnitId());
  }
}
 