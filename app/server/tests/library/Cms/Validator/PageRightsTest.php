<?php
namespace Cms\Validator;

use Cms\Validator\PageRights as PageRightsValidator;
/**
 * PageRightsTest
 *
 * @package      Cms
 * @subpackage   Validator
 */
class PageRightsTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider nonPageIdedRightsProvider
   */
  public function isValidShouldReturnFalseForNonPageIdedRights($rights)
  {
    $rightsJsonString = json_encode($rights);
    $rightsObject = json_decode($rightsJsonString);

    $rightsValidator = new PageRightsValidator;

    $this->assertFalse($rightsValidator->isValid($rightsObject));

    $validationErrors = $rightsValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      PageRightsValidator::INVALID_NO_PAGEID,
      $validationErrors[0]
    );
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function isValidShouldReturnTrueForEmptyRights()
  {
    $emptyRights = new \stdClass();
    $rightsValidator = new PageRightsValidator;
    $this->assertTrue($rightsValidator->isValid($emptyRights));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider validPageRightsProvider
   */
  public function isValidShouldReturnTrueForAllowedPagePrivileges($validPageRight)
  {
    $rightsJsonString = json_encode($validPageRight);
    $rightsObject = json_decode($rightsJsonString);

    $rightsValidator = new PageRightsValidator;
    $this->assertTrue($rightsValidator->isValid($rightsObject));
  }
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   * @dataProvider invalidPageRightsProvider
   */
  public function isValidShouldReturnFalseForNonAllowedPagePrivileges($invalidPageRight)
  {
    $rightsJsonString = json_encode($invalidPageRight);
    $rightsObject = json_decode($rightsJsonString);

    $rightsValidator = new PageRightsValidator;
    $this->assertFalse($rightsValidator->isValid($rightsObject));

    $validationErrors = $rightsValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      PageRightsValidator::INVALID_NON_ALLOWED_PRIVILEGE,
      $validationErrors[0]
    );
  }
  /**
   * @test
   * @group library
   */
  public function isValidShouldReturnFalseForEmptyPagePrivilege()
  {
    $pageWithEmptyPrevilege = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00af-PAGE' => array('edit'),
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array()
    );
    $pageWithEmptyPrevilegeJsonString = json_encode($pageWithEmptyPrevilege);
    $pageWithEmptyPrevilegeObject = json_decode($pageWithEmptyPrevilegeJsonString);

    $rightsValidator = new PageRightsValidator;
    $this->assertFalse($rightsValidator->isValid($pageWithEmptyPrevilegeObject));

    $validationErrors = $rightsValidator->getErrors();

    $this->assertSame(1, count($validationErrors));
    $this->assertSame(
      PageRightsValidator::INVALID_EMPTY_PRIVILEGE,
      $validationErrors[0]
    );
  }
  /**
   * @return array
   */
  public function invalidPageRightsProvider()
  {
    $pageEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('edito'),
    );
    $pageEditAndSubEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('edit', 'subEdito'),
      'PAGE-0db8eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subEdito'),
    );
    $pageSubEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subEdito')
    );
    $pageSubAll = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subAllo')
    );
    return array(
      array($pageEdit),
      array($pageEditAndSubEdit),
      array($pageSubEdit),
      array($pageSubAll),
    );
  }
  /**
   * @return array
   */
  public function validPageRightsProvider()
  {
    $pageEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('edit'),
    );
    $pageEditAndSubEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('edit', 'subEdit'),
      'PAGE-0db8eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subEdit'),
    );
    $pageSubEdit = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subEdit')
    );
    $pageSubAll = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('subAll')
    );
    return array(
      array($pageEdit),
      array($pageEditAndSubEdit),
      array($pageSubEdit),
      array($pageSubAll),
    );
  }
  /**
   * @return array
   */
  public function nonPageIdedRightsProvider()
  {
    $nonPageIdedRightsSingle = array(
      'MODUL-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-MODUL' => array('edit')
    );
    $nonPageIdedRightsMultiple = array(
      'PAGE-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-PAGE' => array('edit'),
      'GROUP-0db7eaa7-7fc5-464a-bd47-16b3b8af00ag-GROUP' => array('edit'),
    );
    return array(
      array($nonPageIdedRightsSingle),
      array($nonPageIdedRightsMultiple)
    );
  }
}