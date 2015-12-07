<?php

namespace library\Cms\Validator;

use Cms\Request\Validator\Base;
use Test\Rukzuk\AbstractTestCase;


class TestBase extends Base {

}


class BaseTest extends AbstractTestCase {

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider provider_isValudShouldReturnTrueForNewTLDs
   * @see https://jira.rukzuk.intern/browse/RZ-2037
   */
  public function isValudShouldReturnTrueForNewTLDs($email, $errorCount)
  {
    // ARANGE
    $baseValidator = new TestBase();
    // ACT
    $this->callMethod($baseValidator, 'validateEmail', array($email));
    // ASSERT
    $this->assertCount($errorCount, $baseValidator->getErrors());
  }

  public function provider_isValudShouldReturnTrueForNewTLDs() {
    return array(
      array('hans@example', 1),
      array('hans@example.com', 0),
      array('hans@example.de', 0),
      array('hans@example.abc', 0),
      array('hans@example.agency', 0),
    );
  }

}
