<?php


namespace Cms\Validator;


class ResolutionsTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider provider_isValidShouldReturnTrueForValidResolutions
   */
  public function isValidShouldReturnTrueForValidResolutions($resolutions)
  {
    //
    // ARRANGE
    //
    $validator = new Resolutions();
    //
    // ACT/ASSERT
    //
    $this->assertTrue($validator->isValid($resolutions));
  }

  /**
   * @return array
   */
  public function provider_isValidShouldReturnTrueForValidResolutions()
  {
    return array(
      array('{"enabled":false,"data":[]}'),
      array('{"enabled":false,"data":[{"width":999,"name":"breakpoint full","id":"res1"}]}'),
      array('{"enabled":true,"data":[{"width":768,"name":"breakpoint 1","id":"res1"},{"width":480,"name":"breakpoint 2","id":"res2"},{"width":320,"name":"breakpoint 3","id":"res3"}]}'),
      array('{"enabled":true,"data":[{"width":500,"name":"breakpoints equal","id":"res1"},{"width":500,"name":"breakpoints equal","id":"res2"}]}'),
    );
  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider provider_isValidShouldReturnFalseForNoJsonStructure
   */
  public function isValidShouldReturnFalseForNoJsonStructure($resolutions)
  {
    //
    // ARRANGE
    //
    $validator = new Resolutions();
    //
    // ACT/ASSERT
    //
    $this->assertFalse($validator->isValid($resolutions));
    $errors = $validator->getErrors();
    $this->assertInternalType('array', $errors);
    $this->assertEquals(1, count($errors));
    $this->assertEquals(Resolutions::INVALID, $errors[0]);
  }

  public function provider_isValidShouldReturnFalseForNoJsonStructure()
  {
    return array(
      array(''),
      array(null),
      array(1),
      array(array()),
      array(new \stdClass()),
      array('NO_JSON_STRUCTURE'),
      array('{"foo":"bar","WRONG":["MISSING ]"}'),
    );
  }
}
 