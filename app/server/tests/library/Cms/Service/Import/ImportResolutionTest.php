<?php


namespace Cms\Service;


use Test\Seitenbau\ServiceTestCase;

class ImportResolutionTest extends ServiceTestCase
{
  /**
   * @test
   * @group library
   * @group small
   * @dataProvider provider_createResolutionsJson_returnValidResolutionsJson
   */
  public function createResolutionsJson_returnValidResolutionsJson($importResolutionsJson,
                                                                   $expectedResolutionsJson)
  {
    //
    // ARRANGE
    //
    $method = new \ReflectionMethod('\Cms\Service\Import', 'createResolutionsJson');
    $method->setAccessible(true);
    $validator = new \Cms\Validator\Resolutions();
    $import = new Import();
    //
    // ACT
    //
    $actualResolutionsJson =  $method->invoke($import, $importResolutionsJson);
    //
    // ASSERT
    //
    $this->assertTrue($validator->isValid($actualResolutionsJson),
      "Validate error(s):\n".implode("\n", $validator->getMessages()));
    $this->assertEquals($expectedResolutionsJson, $actualResolutionsJson);
  }

  /**
   * @return array
   */
  public function provider_createResolutionsJson_returnValidResolutionsJson()
  {
    $defaultResolutionJson = '{"enabled":false,"data":[]}';
    return array(
      array(null, $defaultResolutionJson),
      array('', $defaultResolutionJson),
      array('no valid json', $defaultResolutionJson),
      array('{"invalid_json":false', $defaultResolutionJson),
      array('[]', $defaultResolutionJson),
      array('{"enabled":false}', $defaultResolutionJson),
      array('{"enabled":"yes"}', $defaultResolutionJson),
      array('{"data":[]}', $defaultResolutionJson),
      array('{"enabled":false,"data":{}}', $defaultResolutionJson),
      array('{"enabled":true,"data":[]}', '{"enabled":true,"data":[]}'),
      array(
        '{"enabled":true,"data":[{"width":480}]}',
        '{"enabled":true,"data":[{"width":480,"id":"res1","name":"res1"}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":{"foo":"bar"},"width":480}]}',
        '{"enabled":true,"data":[{"width":480,"id":"res1","name":"res1"}]}',
      ),
      array(
        '{"enabled":true,"data":[{"name":"RES 1","width":480}]}',
        '{"enabled":true,"data":[{"name":"RES 1","width":480,"id":"res1"}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":"res1","name":"RES 1","width":480}]}',
        '{"enabled":true,"data":[{"id":"res1","name":"RES 1","width":480}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":"res1","name":"RES 1","width":480},{"id":"res2","name":"RES 2","width":240}]}',
        '{"enabled":true,"data":[{"id":"res1","name":"RES 1","width":480},{"id":"res2","name":"RES 2","width":240}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":"res2","name":"RES 2","width":240},{"id":"res1","name":"RES 1","width":480}]}',
        '{"enabled":true,"data":[{"id":"res1","name":"RES 1","width":480},{"id":"res2","name":"RES 2","width":240}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":"res2","name":"RES 2","width":240},{"width":480}]}',
        '{"enabled":true,"data":[{"width":480,"id":"res1","name":"res1"},{"id":"res2","name":"RES 2","width":240}]}',
      ),
      array(
        '{"enabled":true,"data":[{"id":"res2","name":"RES 2","width":240},{"width":480},{"width":768}]}',
        '{"enabled":true,"data":[{"width":768,"id":"res1","name":"res1"},{"width":480,"id":"res3","name":"res3"},{"id":"res2","name":"RES 2","width":240}]}',
      ),
    );
  }

  /**
   * @test
   * @group library
   * @group small
   */
  public function createNextResolutionId_breakAndReturnUniqueIdIfCountReachedMaxNumber()
  {
    //
    // ARRANGE
    //
    $method = new \ReflectionMethod('\Cms\Service\Import', 'createNextResolutionId');
    $method->setAccessible(true);
    $import = new Import();
    $existingIds = array();
    for ($i=1; $i <= 101; $i++) {
      $existingIds[] = 'res'.$i;
    }
    //
    // ACT
    //
    $resolutionId =  $method->invoke($import, $existingIds);
    //
    // ASSERT
    //
    $this->assertEquals('resSec', substr($resolutionId, 0, 6));
  }

  /**
   * @test
   * @group library
   * @group small
   */
  public function createNextResolutionId_returnFirstNewResolutionId()
  {
    //
    // ARRANGE
    //
    $method = new \ReflectionMethod('\Cms\Service\Import', 'createNextResolutionId');
    $method->setAccessible(true);
    $import = new Import();
    $existingIds = array();
    //
    // ACT
    //
    $resolutionId =  $method->invoke($import, $existingIds);
    //
    // ASSERT
    //
    $this->assertEquals('res1', $resolutionId);
  }
}
 