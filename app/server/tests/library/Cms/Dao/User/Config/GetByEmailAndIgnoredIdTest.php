<?php


namespace Cms\Dao\User\Config;


use Test\Cms\Dao\Page\AbstractDaoTestCase;
use Test\Rukzuk\ConfigHelper;

class GetByEmailAndIgnoredIdTest extends AbstractDaoTestCase
{
  /**
   * @test
   * @group small
   * @group dev
   * @group library
   */
  public function test_getByEmailAndIgnoredIdShouldReturnExpectedUser()
  {
    // ARRANGE
    $expectedOwner = ConfigHelper::setOwner();
    $dao = $this->getDao();

    // ACT
    $actualUser = $dao->getByEmailAndIgnoredId($expectedOwner['email']);

    // ASSERT
    $this->assertInstanceOf('\Cms\Data\User', $actualUser);
    $this->assertEquals($expectedOwner['id'], $actualUser->getId());

  }

  /**
   * @test
   * @group small
   * @group dev
   * @group library
   *
   * @dataProvider test_getByEmailAndIgnoredIdShouldThrowExceptionIfExpectedUserNotExistsProvider
   *
   * @expectedException \Cms\Dao\User\UserNotFoundException
   * @expectedExceptionCode 1002
   */
  public function test_getByEmailAndIgnoredIdShouldThrowExceptionIfExpectedUserNotExists($ownerEmail,
                                                                                         $ownerId,
                                                                                         $email, $id)
  {
    // ARRANGE
    ConfigHelper::setOwner(array(
      'id' => $ownerId,
      'email' => $ownerEmail,
    ));
    $dao = $this->getDao();

    // ACT
    $dao->getByEmailAndIgnoredId($email, $id);
  }

  /**
   * @return array
   */
  public function test_getByEmailAndIgnoredIdShouldThrowExceptionIfExpectedUserNotExistsProvider()
  {
    return array(
      array('owner@rukzuk.com', null, 'notExists@user.com', null),
      array('owner@rukzuk.com', 'OWNER-ID', 'owner@rukzuk.com', 'OWNER-ID'),
    );
  }

  /**
   * @return \Cms\Dao\User\Config
   */
  protected function getDao()
  {
    return new \Cms\Dao\User\Config();
  }
}
 