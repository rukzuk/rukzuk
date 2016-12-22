<?php


namespace Render;


use Render\InfoStorage\ModuleInfoStorage\ArrayBasedModuleInfoStorage;
use Render\InfoStorage\ContentInfoStorage\ArrayBasedContentInfoStorage;
use Render\Nodes\LegacyNode;


class Test1NodeFactory extends NodeFactory
{

  private  $createNodeObjectCalls = array();

  /**
   * @return array
   */
  public function getCreateNodeObjectCalls()
  {
    return $this->createNodeObjectCalls;
  }

  protected function createNodeObject(array &$content, NodeTree $tree,
                                      array &$usedModuleIds, $parentId = null)
  {
    $moduleId = $content['moduleId'];
    $usedModuleIds[] = $moduleId;
    $unit = $this->getUnit($content);
    $moduleInfo = $this->getModuleInfo($moduleId);
    $node = $this->createLegacyNode($tree, $parentId, $unit, $moduleInfo);

    $this->createNodeObjectCalls[] = array($content, $tree, $parentId, "return" => $node);
    return $node;
  }
}


class NodeTreeTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   * @expectedException \Exception
   * @expectedExceptionMessage Empty unit (content) detected
   */
  public function test_emptyContent()
  {
    $moduleInfos = array();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    $nodeFactory = new NodeFactory($nodeContext);
    $arr = array();
    $nodeTree = new NodeTree($arr, $nodeFactory);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_simpleRootNode()
  {
    //
    // ARRANGE
    //
    // Prepare module info storage
    $moduleInfos = $this->getModuleInfo();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    // Prepare content info storage
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    // Prepare node context
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    // Prepare content
    $content = array();
    $content['id'] = 'UNIT-78667474-aa5c-498c-bcc0-046277bd153b-UNIT';
    $content['name'] = 'SimpleTestUnit';
    $content['moduleId'] = 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL';
    //
    // ACT
    //
    $nodeFactory = new Test1NodeFactory($nodeContext);
    $nodeTree = new NodeTree($content, $nodeFactory);
    //
    // ASSERT
    //
    $this->assertEquals(1, count($nodeFactory->getCreateNodeObjectCalls()));

    $arr = $nodeFactory->getCreateNodeObjectCalls();
    $firstCall = $arr[0];
    $this->assertEquals($content, $firstCall[0]);
    $this->assertNotNull($firstCall[1]);
    $this->assertNull($firstCall[2]);

    $this->assertEquals($firstCall['return'], $nodeTree->getRootNode());
    $this->assertEquals(1, count($nodeTree->getUnitMap()));
  }


  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_normalTree()
  {
    //
    // ARRANGE
    //
    // Prepare module info storage
    $moduleInfos = $this->getModuleInfo();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfos);
    // Prepare content info storage
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    // Prepare node context
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    // Children Level 2
    $l2 = array();
    $l2['id'] = 'UNIT-d4f8dd99-c7b7-48a4-825c-856976138a08-UNIT';
    $l2['name'] = 'SimpleTestUnit';
    $l2['moduleId'] = 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL';
    // Children Level 1
    $l1 = array();
    $l1['id'] = 'UNIT-b91b7599-76ae-4b29-85dc-6656720a816d-UNIT';
    $l1['name'] = 'SimpleTestUnit';
    $l1['moduleId'] = 'MODUL-d5268b2c-08b5-4e95-8fe4-9f4e96d50688-MODUL';
    $l1['children'] = array($l2);

    $l1_2 = array();
    $l1_2['id'] = 'UNIT-d81a34f2-02df-4f1c-80a3-d949e7848cef-UNIT';
    $l1_2['name'] = 'SimpleTestUnit';
    $l1_2['moduleId'] = 'MODUL-23c3113e-31ee-42a9-b653-c82e79e77aa5-MODUL';

    // Prepare content
    $content = array();
    $content['id'] = 'UNIT-78667474-aa5c-498c-bcc0-046277bd153b-UNIT';
    $content['name'] = 'SimpleTestUnit';
    $content['moduleId'] = 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL';
    $content['children'] = array($l1, $l1_2);
    //
    // ACT
    //
    $nodeFactory = new Test1NodeFactory($nodeContext);
    $nodeTree = new NodeTree($content, $nodeFactory);
    //
    // ASSERT
    //
    $createNodeObjectCalls = $nodeFactory->getCreateNodeObjectCalls();
    $this->assertEquals(4, count($createNodeObjectCalls));

    $lastCall = $createNodeObjectCalls[3];
    $this->assertEquals($l1_2, $lastCall[0]);
    $this->assertNotNull($lastCall[1]);
    $this->assertEquals($content['id'], $lastCall[2]);
    $lastNode = $lastCall['return'];
    $this->assertEmpty($lastNode->getChildren());

    $thirdCall = $createNodeObjectCalls[2];
    $this->assertEquals($l2, $thirdCall[0]);
    $this->assertNotNull($thirdCall[1]);
    $this->assertEquals($l1['id'], $thirdCall[2]);
    $thirdNode = $thirdCall['return'];
    $this->assertEmpty($thirdNode->getChildren());

    $secondCall = $createNodeObjectCalls[1];
    $this->assertEquals($l1, $secondCall[0]);
    $this->assertNotNull($secondCall[1]);
    $this->assertEquals($content['id'], $secondCall[2]);
    $secondNode = $secondCall['return'];
    $this->assertEquals(array($thirdNode), $secondNode->getChildren());

    // Root Node
    $firstCall = $createNodeObjectCalls[0];
    $this->assertEquals($content, $firstCall[0]);
    $this->assertNotNull($firstCall[1]);
    $this->assertNull($firstCall[2]);
    $rootNode = $firstCall['return'];
    $this->assertEquals(array($secondNode, $lastNode), $rootNode->getChildren());

    $root = $nodeTree->getRootNode();
    $unit = $root->getUnit()->toArray();
    $this->assertEquals($content['moduleId'], $unit['moduleId']);

    $root = $nodeTree->getRootNode();
    $l1Nodes = $root->getChildren();
    $l1unit = $l1Nodes[0]->getUnit()->toArray();
    $this->assertEquals($l1['moduleId'], $l1unit['moduleId']);

    $root = $nodeTree->getRootNode();
    $l1Nodes = $root->getChildren();
    $l1_2unit = $l1Nodes[1]->getUnit()->toArray();
    $this->assertEquals($l1_2['moduleId'], $l1_2unit['moduleId']);

    $l1Node = $l1Nodes[0];
    $l2Nodes = $l1Node->getChildren();
    $l2unit = $l2Nodes[0]->getUnit()->toArray();
    $this->assertEquals($l2['moduleId'], $l2unit['moduleId']);

    $this->assertEquals($createNodeObjectCalls[0]['return'],
      $nodeTree->getRootNode());
    $this->assertEquals(4, count($nodeTree->getUnitMap()));
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getRootNode_success()
  {
    //
    // ARRANGE
    //
    // Prepare module info storage
    $moduleInfo = $this->getModuleInfo();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfo);
    // Prepare content info storage
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    // Prepare node context
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    // Prepare content
    $content = $this->getContent();

    //
    // ACT
    //
    $nodeFactory = new Test1NodeFactory($nodeContext);
    $nodeTree = new NodeTree($content, $nodeFactory);

    //
    // ASSERT
    //
    $rootNode = $nodeTree->getRootNode();
    $this->assertEquals($content['id'], $rootNode->getUnitId());
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_getNodeByUnitId_success()
  {
    //
    // ARRANGE
    //
    // Prepare module info storage
    $moduleInfo = $this->getModuleInfo();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfo);
    // Prepare content info storage
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    // Prepare node context
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    // Prepare content
    $content = $this->getContent();

    //
    // ACT
    //
    $nodeFactory = new Test1NodeFactory($nodeContext);
    $nodeTree = new NodeTree($content, $nodeFactory);

    //
    // ASSERT
    //
    $unitContent1 = $content;
    $node1 = $nodeTree->getNodeByUnitId($unitContent1['id']);
    $this->assertEquals($unitContent1['id'], $node1->getUnitId());

    $unitContent2 = $content['children'][0]['children'][0];
    $node2 = $nodeTree->getNodeByUnitId($unitContent2['id']);
    $this->assertEquals($unitContent2['id'], $node2->getUnitId());
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   *
   * @expectedException \Render\Exceptions\NodeNotFoundException
   */
  public function test_getNodeByUnitId_throwExceptionIfNodeNotExists()
  {
    //
    // ARRANGE
    //
    // Prepare module info storage
    $moduleInfo = $this->getModuleInfo();
    $moduleInfoStorage = new ArrayBasedModuleInfoStorage($moduleInfo);
    // Prepare content info storage
    $templateInfos = array();
    $contentInfoStorage = new ArrayBasedContentInfoStorage($templateInfos);
    // Prepare node context
    $nodeContext = new NodeContext($moduleInfoStorage, $contentInfoStorage, null, null);
    // Prepare content
    $content = $this->getContent();

    //
    // ACT
    //
    $nodeFactory = new Test1NodeFactory($nodeContext);
    $nodeTree = new NodeTree($content, $nodeFactory);

    //
    // ASSERT
    //
    $nodeTree->getNodeByUnitId('UNIT-NOT-EXIST-ID');
  }

  /**
   * @return array
   */
  private function getModuleInfo()
  {
    $moduleId = 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL';
    $moduleClassName = 'TestNameSpace\TestClass';
    $formValues = array("x" => 1);
    $moduleMainClassFilePath = "dataDir/testclass.module.php";
    $moduleInfo = array();
    $moduleInfo[$moduleId] = array(
      'mainClassName' => $moduleClassName,
      'moduleFormValues' => $formValues,
      'mainClassFilePath' => $moduleMainClassFilePath,
      'manifest' => null,
      'customData' => null,
    );
    $moduleId = 'MODUL-d5268b2c-08b5-4e95-8fe4-9f4e96d50688-MODUL';
    $moduleClassName = 'TestNameSpace\TestClass';
    $formValues = array("x" => 1);
    $moduleMainClassFilePath = "dataDir/testclass.module.php";
    $moduleInfo[$moduleId] = array(
      'mainClassName' => $moduleClassName,
      'moduleFormValues' => $formValues,
      'mainClassFilePath' => $moduleMainClassFilePath,
      'manifest' => null,
      'customData' => null,
    );
    $moduleId = 'MODUL-23c3113e-31ee-42a9-b653-c82e79e77aa5-MODUL';
    $moduleClassName = 'TestNameSpace\TestClass';
    $formValues = array("x" => 1);
    $moduleMainClassFilePath = "dataDir/testclass.module.php";
    $moduleInfo[$moduleId] = array(
      'mainClassName' => $moduleClassName,
      'moduleFormValues' => $formValues,
      'mainClassFilePath' => $moduleMainClassFilePath,
      'manifest' => null,
      'customData' => null,
    );
    return $moduleInfo;
  }

  /**
   * @return array
   */
  private function getContent()
  {
    // Children Level 3
    $l3 = array(
      'id' => 'UNIT-00000000-0000-0000-0000-111000000000-UNIT',
      'name' => 'SimpleTestUnit Level 3',
      'moduleId' => 'MODUL-23c3113e-31ee-42a9-b653-c82e79e77aa5-MODUL',
    );
    // Children Level 2
    $l2 = array(
      'id' => 'UNIT-00000000-0000-0000-0000-110000000000-UNIT',
      'name' => 'SimpleTestUnit Level 2',
      'moduleId' => 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL',
      'children' => array($l3),
    );
    // Children Level 1
    $l1 = array(
      'id' => 'UNIT-00000000-0000-0000-0000-100000000000-UNIT',
      'name' => 'SimpleTestUnit Level 1',
      'moduleId' => 'MODUL-d5268b2c-08b5-4e95-8fe4-9f4e96d50688-MODUL',
      'children' => array($l2),
    );
    // Prepare Content
    $content = array(
      'id' => 'UNIT-00000000-0000-0000-0000-000000000000-UNIT',
      'name' => 'SimpleTestUnit Root Unit',
      'moduleId' => 'MODUL-86a089bd-9d94-440c-b409-6d0be21836d2-MODUL',
      'children' => array($l1),
    );
    return $content;
  }
}
