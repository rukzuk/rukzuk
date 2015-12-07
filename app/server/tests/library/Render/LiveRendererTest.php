<?php


namespace Render;


use Test\Rukzuk\AbstractTestCase;

class LiveRendererTest extends AbstractTestCase
{
  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_renderHtml_shouldCall_isSessionRequired_and_startSession()
  {
    $rootNode = $this->createNodeMock();
    $htmlVisitorMock = $this->createHtmlVisitorMock();
    $renderer = $this->createLiveRendererMock(array('getRootNode', 'getHtmlVisitor',
      'isSessionRequired', 'startSession'));
    $renderer->expects($this->once())
      ->method('getRootNode')
      ->will($this->returnValue($rootNode));
    $renderer->expects($this->once())
      ->method('getHtmlVisitor')
      ->will($this->returnValue($htmlVisitorMock));
    $renderer->expects($this->once())
      ->method('isSessionRequired')
      ->will($this->returnValue(true));
    $renderer->expects($this->once())
      ->method('startSession');

    // ACT
    $renderer->renderHtml();
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_renderHtml_shouldCallOnly_isSessionRequired()
  {
    $rootNode = $this->createNodeMock();
    $htmlVisitorMock = $this->createHtmlVisitorMock();
    $renderer = $this->createLiveRendererMock(array('getRootNode', 'getHtmlVisitor',
      'isSessionRequired', 'startSession'));
    $renderer->expects($this->once())
      ->method('getRootNode')
      ->will($this->returnValue($rootNode));
    $renderer->expects($this->once())
      ->method('getHtmlVisitor')
      ->will($this->returnValue($htmlVisitorMock));
    $renderer->expects($this->once())
      ->method('isSessionRequired')
      ->will($this->returnValue(false));
    $renderer->expects($this->never())
      ->method('startSession');

    // ACT
    $renderer->renderHtml();
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_isSessionRequired_returnsFalseIfNoModuleRequiredSession()
  {
    // ARRANGE
    $modulesManifest = array(
      'MODULE_ID_1' => array('sessionRequired' => false),
      'MODULE_ID_2' => array('sessionRequired' => false),
      'MODULE_ID_3' => array('sessionRequired' => false),
      'MODULE_ID_4' => array('sessionRequired' => false),
    );
    $moduleIds = array_keys($modulesManifest);
    shuffle($moduleIds);

    $moduleInfoStorageMock = $this->createModuleInfoStorageMock();
    $moduleInfoStorageMock->expects($this->any())
      ->method('getModuleManifest')
      ->will($this->returnCallback(function ($moduleId) use (&$modulesManifest) {
        if (!isset($modulesManifest[$moduleId])) {
          throw new \Exception('Missing module manifest mock for id "'.$moduleId.'"');
        }
        return $modulesManifest[$moduleId];
      }));

    $nodeTreeMock = $this->createNodeTreeMock();
    $nodeTreeMock->expects($this->once())
      ->method('getUsedModuleIds')
      ->will($this->returnValue($moduleIds));

    $renderer = $this->createLiveRendererMock(array('getNodeTree', 'getModuleInfoStorage'));
    $renderer->expects($this->once())
      ->method('getNodeTree')
      ->will($this->returnValue($nodeTreeMock));
    $renderer->expects($this->once())
      ->method('getModuleInfoStorage')
      ->will($this->returnValue($moduleInfoStorageMock));


    // ACT
    $sessionRequired = $this->callMethod($renderer, 'isSessionRequired');

    // ASSERT
    $this->assertFalse($sessionRequired);
  }

  /**
   * @test
   * @group rendering
   * @group small
   * @group dev
   */
  public function test_isSessionRequired_returnsTrueIfAtLeastOneModuleRequiredSession()
  {
    // ARRANGE
    $modulesManifest = array(
      'MODULE_ID_1' => array('sessionRequired' => false),
      'MODULE_ID_2' => array('sessionRequired' => false),
      'MODULE_ID_3' => array('sessionRequired' => true),
      'MODULE_ID_4' => array('sessionRequired' => false),
    );
    $moduleIds = array_keys($modulesManifest);

    $moduleInfoStorageMock = $this->createModuleInfoStorageMock();
    $moduleInfoStorageMock->expects($this->any())
      ->method('getModuleManifest')
      ->will($this->returnCallback(function ($moduleId) use (&$modulesManifest) {
        if (!isset($modulesManifest[$moduleId])) {
          throw new \Exception('Missing module manifest mock for id "'.$moduleId.'"');
        }
        return $modulesManifest[$moduleId];
      }));

    $nodeTreeMock = $this->createNodeTreeMock();
    $nodeTreeMock->expects($this->once())
      ->method('getUsedModuleIds')
      ->will($this->returnValue($moduleIds));

    $renderer = $this->createLiveRendererMock(array('getNodeTree', 'getModuleInfoStorage'));
    $renderer->expects($this->once())
      ->method('getNodeTree')
      ->will($this->returnValue($nodeTreeMock));
    $renderer->expects($this->once())
      ->method('getModuleInfoStorage')
      ->will($this->returnValue($moduleInfoStorageMock));


    // ACT
    $sessionRequired = $this->callMethod($renderer, 'isSessionRequired');

    // ASSERT
    $this->assertTrue($sessionRequired);
  }

  /**
   * @param array $methods
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|LiveRenderer
   */
  protected function createLiveRendererMock(array $methods = null)
  {
    $mockBuilder = $this->getMockBuilder('\Render\LiveRenderer')->disableOriginalConstructor();
    if (is_array($methods)) {
      $mockBuilder->setMethods($methods);
    }
    return $mockBuilder->getMock();
  }

  /**
   * @param array $methods
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|NodeTree
   */
  protected function createNodeTreeMock(array $methods = null)
  {
    $mockBuilder = $this->getMockBuilder('\Render\NodeTree')->disableOriginalConstructor();
    if (is_array($methods)) {
      $mockBuilder->setMethods($methods);
    }
    return $mockBuilder->getMock();
  }

  /**
   * @param array $methods
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|Nodes\INode
   */
  protected function createNodeMock(array $methods = null)
  {
    $mockBuilder = $this->getMockBuilder('\Render\Nodes\INode')->disableOriginalConstructor();
    if (is_array($methods)) {
      $mockBuilder->setMethods($methods);
    }
    return $mockBuilder->getMock();
  }

  /**
   * @param array $methods
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|Visitors\HtmlVisitor
   */
  protected function createHtmlVisitorMock(array $methods = null)
  {
    $mockBuilder = $this->getMockBuilder('\Render\Visitors\HtmlVisitor')->disableOriginalConstructor();
    if (is_array($methods)) {
      $mockBuilder->setMethods($methods);
    }
    return $mockBuilder->getMock();
  }

  /**
   * @param array $methods
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|InfoStorage\ModuleInfoStorage\IModuleInfoStorage
   */
  protected function createModuleInfoStorageMock(array $methods = null)
  {
    $mockBuilder = $this->getMockBuilder('\Render\InfoStorage\ModuleInfoStorage\IModuleInfoStorage')
      ->disableOriginalConstructor();
    if (is_array($methods)) {
      $mockBuilder->setMethods($methods);
    }
    return $mockBuilder->getMock();
  }

}
 