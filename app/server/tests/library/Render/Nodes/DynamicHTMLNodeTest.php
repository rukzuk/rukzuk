<?php


namespace Render\Nodes;

use Render\InfoStorage\ContentInfoStorage\ArrayBasedContentInfoStorage;
use Render\NodeContext;
use Test\Render\AbstractRenderTestCase;
use Render\InfoStorage\ModuleInfoStorage\ArrayBasedModuleInfoStorage;
use Render\NodeTree;
use Render\Visitors\AbstractVisitor;
use Test\Render\SimpleTestNodeFactory;


class DynamicHTMLNodeTestVisitor extends AbstractVisitor
{
  protected $dynamicHTMLNodes = array();

  protected $legacyNodes = array();

  /**
   * @return array
   */
  public function getDynamicHTMLNodes()
  {
    return $this->dynamicHTMLNodes;
  }

  /**
   * @return array
   */
  public function getLegacyNodes()
  {
    return $this->legacyNodes;
  }

  public function visitDynamicHTMLNode(DynamicHTMLNode $node)
  {
    $this->dynamicHTMLNodes[] = $node;
  }

  public function visitLegacyNode(LegacyNode $node)
  {
    $this->legacyNodes[] = $node;
  }
}


class DynamicHTMLNodeTest extends AbstractRenderTestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_accept()
  {
    // ARRANGE
    $interfaceLocaleCode = 'de-AT';
    $nodeTree = $this->getTestNodeTree();
    $node = $nodeTree->getRootNode();
    $renderContext = $this->createRenderContext(array(
      'interfaceLocaleCode' => $interfaceLocaleCode,
    ));
    $visitor = new DynamicHTMLNodeTestVisitor($renderContext);
    // ACT
    $node->accept($visitor);
    // ASSERT
    $this->assertInstanceOf(__NAMESPACE__.'\DynamicHTMLNode', $node);
    $this->assertEquals(array($node), $visitor->getDynamicHTMLNodes());
    $this->assertEmpty($visitor->getLegacyNodes());
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_provideUnitData()
  {
    // ARRANGE
    $nodeTree = $this->getTestNodeTree();
    $node = $nodeTree->getRootNode();
    $sentinel = 'sentinel';
    // ACT
    /** @var $node DynamicHTMLNode */
    $node->provideUnitData($sentinel);
    // ASSERT
    /** @var $module TestModule */
    $module = $node->getModule();
    $unit = $node->getUnit();
    $moduleInfo = $node->getModuleInfo();
    $calls = $module->getCalls();
    $this->assertEquals(1, count($calls));
    $this->assertEquals(
      array('Test\Render\SimpleTestModule::provideUnitData', 'sentinel', $unit, $moduleInfo),
      $calls[0]);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_provideModuleData()
  {
    // ARRANGE
    $nodeTree = $this->getTestNodeTree();
    $node = $nodeTree->getRootNode();
    $sentinel = 'sentinel';
    // ACT
    /** @var $node DynamicHTMLNode */
    $node->provideModuleData($sentinel);
    // ASSERT
    /** @var $module TestModule */
    $module = $node->getModule();
    $moduleInfo = $node->getModuleInfo();
    $calls = $module->getCalls();
    $this->assertEquals(1, count($calls));
    $this->assertEquals(
      array('Test\Render\SimpleTestModule::provideModuleData', 'sentinel', $moduleInfo),
      $calls[0]);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_renderHtmlOutput()
  {
    // ARRANGE
    $nodeTree = $this->getTestNodeTree();
    $node = $nodeTree->getRootNode();
    $sentinel = 'sentinel';
    // ACT
    /** @var $node DynamicHTMLNode */
    $node->renderHtmlOutput($sentinel);
    // ASSERT
    /** @var $module TestModule */
    $module = $node->getModule();
    $unit = $node->getUnit();
    $moduleInfo = $node->getModuleInfo();
    $calls = $module->getCalls();
    $this->assertEquals(1, count($calls));
    $this->assertEquals(
      array('Test\Render\SimpleTestModule::render', 'sentinel', $unit, $moduleInfo),
      $calls[0]);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_renderCssOutput()
  {
    // ARRANGE
    $nodeTree = $this->getTestNodeTree();
    $node = $nodeTree->getRootNode();
    $sentinel = 'sentinel';
    // ACT
    /** @var $node DynamicHTMLNode */
    $node->renderCssOutput($sentinel);
    // ASSERT
    /** @var $module TestModule */
    $module = $node->getModule();
    $unit = $node->getUnit();
    $moduleInfo = $node->getModuleInfo();
    $calls = $module->getCalls();
    $this->assertEquals(1, count($calls));
    $this->assertEquals(
      array('Test\Render\SimpleTestModule::css', 'sentinel', $unit, $moduleInfo),
      $calls[0]);
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
  "children": []
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


