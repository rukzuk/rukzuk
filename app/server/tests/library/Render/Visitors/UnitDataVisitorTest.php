<?php


namespace Render\Visitors;


use Test\Render\AbstractRenderTestCase;
use Render\InfoStorage\ModuleInfoStorage\ArrayBasedModuleInfoStorage;
use Render\NodeTree;
use Render\RenderContext;
use Test\Render\SimpleTestNodeFactory;

class UnitDataVisitorTest extends AbstractRenderTestCase {

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_collectUnitData() {
    $interfaceLocaleCode = 'de-CH';
    $tree = $this->getTestNodeTree();
    $renderContext = $this->createRenderContext(array(
      'interfaceLocaleCode' => $interfaceLocaleCode,
    ));
    $visitor = new UnitDataVisitor($renderContext);
    $tree->getRootNode()->accept($visitor);
    $data = $visitor->getUnitData();
    $this->assertEquals(4, count($data));
    $this->assertEquals(
      array('unitId' => 'MUNIT-f59c2b72-bb08-4a52-962a-e005d228451b-MUNIT'),
      $data['MUNIT-f59c2b72-bb08-4a52-962a-e005d228451b-MUNIT']
    );
    $this->assertEquals(
      array('unitId' => 'MUNIT-ff0ef8ca-0d1d-4781-bbbb-ea24caa5c475-MUNIT'),
      $data['MUNIT-ff0ef8ca-0d1d-4781-bbbb-ea24caa5c475-MUNIT']
    );
    $this->assertEquals(
      array('unitId' => 'MUNIT-ee0ef8ca-0d1d-4781-aaaa-ea24caa5c475-MUNIT'),
      $data['MUNIT-ee0ef8ca-0d1d-4781-aaaa-ea24caa5c475-MUNIT']
    );
    $this->assertEquals(
      null,
      $data['MUNIT-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MUNIT']
    );
  }

  protected function getTestNodeTree()
  {
    $contentString = <<<EOF
{
  "id": "MUNIT-f59c2b72-bb08-4a52-962a-e005d228451b-MUNIT",
  "moduleId": "rz_root",
  "name": "",
  "formValues": {},
  "expanded": true,
  "children": [
    {
        "id": "MUNIT-ff0ef8ca-0d1d-4781-bbbb-ea24caa5c475-MUNIT",
        "moduleId": "rz_date",
        "name": "",
        "formValues": {},
        "expanded": true
    },
    {
        "id": "MUNIT-ee0ef8ca-0d1d-4781-aaaa-ea24caa5c475-MUNIT",
        "moduleId": "rz_date",
        "name": "",
        "formValues": {},
        "expanded": true
    },
    {
        "id": "MUNIT-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MUNIT",
        "moduleId": "MODUL-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MODUL",
        "name": "",
        "formValues": {},
        "expanded": true
    }
  ]
}
EOF;
    $content = json_decode($contentString, true);
    $moduleData = array(
      'rz_root' => array(
        'mainClassFilePath' => '',
        'mainClassName' => '',
        'manifest' => array(
          'apiType' => 'APIv1'
        )
      ),
      'rz_date' => array(
        'mainClassFilePath' => '',
        'mainClassName' => '',
        'manifest' => array(
          'apiType' => 'APIv1'
        )
      ),
      'MODUL-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MODUL' => array(
        'mainClassFilePath' => '',
        'mainClassName' => '',
        'manifest' => array(
          'apiType' => null
        )
      )
    );
    $infoStorage = new ArrayBasedModuleInfoStorage($moduleData);
    $nodeFactory = new SimpleTestNodeFactory($infoStorage);
    return new NodeTree($content, $nodeFactory);
  }

}
 