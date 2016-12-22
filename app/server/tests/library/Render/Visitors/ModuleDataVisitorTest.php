<?php


namespace Render\Visitors;

use Render\InfoStorage\ContentInfoStorage\ArrayBasedContentInfoStorage;
use Render\NodeContext;
use Test\Render\AbstractRenderTestCase;
use Render\InfoStorage\ModuleInfoStorage\ArrayBasedModuleInfoStorage;
use Render\Nodes\LegacyNode;
use Render\NodeTree;
use Render\RenderContext;
use Test\Render\SimpleTestNodeFactory;


class LegacyTestableModuleDataVisitor extends ModuleDataVisitor
{

  /**
   * This method is overwritten to test the legacy node traversing without
   * a real legacy module, which are nearly untestable.
   *
   * @param LegacyNode $node
   *
   * @return string
   */
  protected function getLegacyModuleHeaderOutput(LegacyNode $node)
  {
    return "<!-- HTML header of ". $node->getModuleId() ." -->";
  }

}


class ModuleDataVisitorTest extends AbstractRenderTestCase {

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_collectModuleData() {
    $interfaceLocaleCode = 'de-CH';
    $tree = $this->getTestNodeTree();
    $renderContext = $this->createRenderContext(array(
      'interfaceLocaleCode' => $interfaceLocaleCode,
    ));

    $visitor = new LegacyTestableModuleDataVisitor($renderContext);
    $tree->getRootNode()->accept($visitor);
    $data = $visitor->getModuleData();
    $this->assertEquals(3, count($data));
    $this->assertEquals(array('moduleId' => 'rz_root'), $data['rz_root']);
    $this->assertEquals(array('moduleId' => 'rz_date'), $data['rz_date']);
    $this->assertEquals(
      array(
        'header' =>
          '<!-- HTML header of MODUL-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MODUL -->'
      ),
      $data['MODUL-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MODUL']
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
        "id": "MUNIT-bb0ef8ca-0d1d-4781-aaaa-ea24caa5c475-MUNIT",
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
          'apiType' => 'APIv1',
          'config' => array()
        )
      ),
      'rz_date' => array(
        'mainClassFilePath' => '',
        'mainClassName' => '',
        'manifest' => array(
          'apiType' => 'APIv1',
          'config' => array()
        )
      ),
      'MODUL-240ef8ca-0d1d-4781-9d38-ea24caa5c475-MODUL' => array(
        'mainClassFilePath' => '',
        'mainClassName' => '',
        'manifest' => array(
          'apiType' => null,
          'config' => array()
        )
      )
    );
    $templateData = array();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleData);
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateData);
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    $nodeFactory = new SimpleTestNodeFactory($nodeContext);
    return new NodeTree($content, $nodeFactory);
  }

}
 