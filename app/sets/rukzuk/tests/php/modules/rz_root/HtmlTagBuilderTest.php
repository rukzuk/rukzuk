<?php

use \Test\Rukzuk\ModuleTestCase;
use \Rukzuk\Modules\HtmlTagBuilder;

class HtmlTagBuilderTest extends ModuleTestCase
{
  public function testBuildBooleanAttribute()
  {
    // prepare
    $htmlTagBuilder = new HtmlTagBuilder('iframe', array(
      'class' => 'videoWrapper',
      'allowfullscreen' => null
    ));
    // execute
    $tag = $htmlTagBuilder->toString();
    // verify
    $this->assertContains('<iframe class="videoWrapper" allowfullscreen></iframe>', $tag);
  }

}
