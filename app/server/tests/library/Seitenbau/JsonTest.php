<?php
namespace Seitenbau;

use Seitenbau\Json as SbJson;

/**
 * Komponententest für Seitenbau\Json
 *
 * @package      Seitenbau
 * @subpackage   JsonTest
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group library
   * @dataProvider encodeSuccessProvider
   */
  public function encodeSuccess($value, $expectedResult)
  {
    $actualResult = SbJson::encode($value, false);
    $this->assertEquals($expectedResult, $actualResult);
  }

  /**
   * @test
   * @group library
   */
  public function prettyPrintSuccess()
  {
    $json = '{"foo":"bar","bar":"foo","world":42,"expectNull":null,"expectEmptyArray":[],"expectEmptyObject":{},"expectArray":["data"],"expectObject":{"hasData":true}}';
    $excpetedPrettyPrint =
            "{\n    \"foo\":\"bar\",\n    \"bar\":\"foo\",\n    \"world\":42,\n" .
            "    \"expectNull\":null,\n" .
            "    \"expectEmptyArray\":[\n        \n    ],\n" .
            "    \"expectEmptyObject\":{\n        \n    },\n" .
            "    \"expectArray\":[\n        \"data\"\n    ],\n" .
            "    \"expectObject\":{\n        \"hasData\":true\n    }\n}";
    
    $actualPrettyPrint = SbJson::prettyPrint($json, '    ');
    $this->assertEquals($excpetedPrettyPrint, $actualPrettyPrint);
  }
  
  /**
   * @test
   * @group library
   * @dataProvider decodeSuccessProvider
   */
  public function decodeSuccess($value, $expectedResult, $type)
  {
    $actualResult = SbJson::decode($value, $type);
    $this->assertEquals($expectedResult, $actualResult);
  }

  /**
   * @return array
   */
  public function encodeSuccessProvider()
  {
    $subObject = new \stdClass();
    $subObject->hasData = true;
    
    $dataObject = new \stdClass();
    $dataObject->foo = 'bar';
    $dataObject->bar = 'foo';
    $dataObject->world = 42;
    $dataObject->expectNull = null;
    $dataObject->expectEmptyArray = array();
    $dataObject->expectEmptyObject = new \stdClass();
    $dataObject->expectArray = array('data');
    $dataObject->expectObject = $subObject;
    
    return array(
      array(true, 'true'),
      array(false, 'false'),
      array('', '""'),
      array(null, 'null'),
      array('null', '"null"'),
      array(array(true, 'Foo', 42, array('Bar' => 23)),  '[true,"Foo",42,{"Bar":23}]'),
      array($dataObject, '{"foo":"bar","bar":"foo","world":42,"expectNull":null,"expectEmptyArray":[],"expectEmptyObject":{},"expectArray":["data"],"expectObject":{"hasData":true}}'),
    );
  }

  /**
   * @return array
   */
  public function decodeSuccessProvider()
  {
    $dataObject = new \stdClass();
    $dataObject->integerData = 23;
    $dataObject->stringData = 'Foo';
    
    return array(
      array('[true,"Foo",42,{"Bar":23}]', array(true, 'Foo', 42, array('Bar' => 23)), SbJson::TYPE_ARRAY),
      array('true', true, SbJson::TYPE_OBJECT),
      array('false', false, SbJson::TYPE_OBJECT),
      array('null', null, SbJson::TYPE_OBJECT),
      array('"null"', 'null', SbJson::TYPE_OBJECT),
      array('{"integerData":23,"stringData":"Foo"}', $dataObject, SbJson::TYPE_OBJECT),
    );
  }
}