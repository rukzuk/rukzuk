<?php

use \Test\Rukzuk\RenderApiMock;
use \Test\Rukzuk\ModuleTestCase;

require_once(MODULE_PATH.'/<%= moduleId %>/module/<%= moduleId %>.php');

class <%= moduleId %>_ModuleTest_TestModule extends \Rukzuk\Modules\<%= moduleId %>
{
  // override module class methods here...
}

class <%= moduleId %>_ModuleTest_ApiMock extends RenderApiMock
{
  // override api mock methods here...
}

class <%= moduleId %>_ModuleTest extends ModuleTestCase
{

  protected $moduleNS = '';
  protected $moduleClass = '<%= moduleId %>_ModuleTest_TestModule';

  public function testRender()
  {
    $this->fail('TODO: implement module render test for <%= moduleId %>');
  }
}
