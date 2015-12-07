<?php
namespace Application\Controller\Group;

use Test\Seitenbau\Cms\Response as Response,
    Test\Seitenbau\ControllerTestCase;
/**
 * SetPageRightsTest
 *
 * @package      Test
 * @subpackage   Controller
 */
class SetPageRightsTest extends ControllerTestCase
{
  public $sqlFixtures = array('generic_access_rights.json');

  /**
   * @test
   * @group integration
   * @dataProvider invalidGroupIdsProvider
   */
  public function setPageRightsShouldReturnValidationErrorForInvalidGroupIds($groupId)
  {
    $websiteId = 'SITE-ce6e702f-10ac-4e1e-951f-307e4b8765al-SITE';
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":true,"rights":"[]"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('id', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   * @dataProvider invalidWebsiteIdsProvider
   */
  public function setPageRightsShouldReturnValidationErrorForInvalidWebsiteIds($websiteId)
  {
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg7-GROUP';
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":false,"rights":"[]"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('websiteid', $responseError[0]->param->field);
  }
  /**
   * @test
   * @group integration
   */
  public function setPageRightsShouldReturnErrorForNonExistingGroup()
  {
    $rights = array();
    $websiteId = 'SITE-edl54f03-pr01-4fdb-af34-72ebr0878rg7-SITE';
    $groupId = 'GROUP-edl54f03-pr01-4fdb-af34-72ebr0878rg7-GROUP';
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":false,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $errors = $response->getError();
    $this->assertObjectHasAttribute('code', $errors[0]);
  }
  /**
   * @test
   * @group integration
   */
  public function setPageRightsShouldOverwriteExistingPageRights()
  {
    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg9-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg9-GROUP';

    $formerGroupRightsJson = '[{"area":"website","privilege":"none","ids":null}'
      . ',{"area":"templates","privilege":"all","ids":null}'
      . ',{"area":"modules","privilege":"none","ids":null}'
      . ',{"area":"pages","privilege":"edit","ids":["PAGE-12547dea-owb0-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-owb1-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-owb2-mk32-m111-92e7a4ba3703-PAGE"]}'
      . ',{"area":"pages","privilege":"subAll","ids":["PAGE-12547dea-owb4-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-owb5-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-owb6-mk32-m111-92e7a4ba3703-PAGE"]}]';

    $expectedFormerGroupRights = json_decode($formerGroupRightsJson);

    $request = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $formerGroupRights = $responseData->rights;

    $this->assertInternalType('array', $formerGroupRights);
    foreach ($expectedFormerGroupRights as $rightZaehler => $expectedFormerGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $formerGroupRights);
      $expectedFormerGroupRightValues = get_object_vars($expectedFormerGroupRight);

      foreach ($expectedFormerGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $formerGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $formerGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }



    $alteredGroupRightsJson = '[{"area":"website","privilege":"none","ids":null}'
      . ',{"area":"templates","privilege":"all","ids":null}'
      . ',{"area":"modules","privilege":"none","ids":null}'
      . ',{"area":"pages","privilege":"all","ids":null}'
      . ',{"area":"pages","privilege":"edit","ids":["PAGE-12547dea-own0-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-own1-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-own2-mk32-m111-92e7a4ba3703-PAGE"]}'
      . ',{"area":"pages","privilege":"subAll","ids":["PAGE-12547dea-own3-mk32-m111-92e7a4ba3703-PAGE","PAGE-12547dea-own4-mk32-m111-92e7a4ba3703-PAGE"]}]';

    $expectedAlteredGroupRights = json_decode($alteredGroupRightsJson);

    $rights = array(
      'PAGE-12547dea-own0-mk32-m111-92e7a4ba3703-PAGE' => array('edit'),
      'PAGE-12547dea-own1-mk32-m111-92e7a4ba3703-PAGE' => array('edit'),
      'PAGE-12547dea-own2-mk32-m111-92e7a4ba3703-PAGE' => array('edit'),
      'PAGE-12547dea-own3-mk32-m111-92e7a4ba3703-PAGE' => array('subAll'),
      'PAGE-12547dea-own4-mk32-m111-92e7a4ba3703-PAGE' => array('subAll'),
    );

    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":true,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $alteredGroupRights = $responseData->rights;
    $this->assertInternalType('array', $alteredGroupRights);
    foreach ($expectedAlteredGroupRights as $rightZaehler => $expectedAlteredGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $alteredGroupRights);
      $expectedAlterGroupRightValues = get_object_vars($expectedAlteredGroupRight);

      foreach ($expectedAlterGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $alteredGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $alteredGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function setPageRightsShouldRemovePagesAllRights()
  {
    $formerGroupRightsJson = '[{"area":"website","privilege":"publish","ids":null},'
      . '{"area":"templates","privilege":"all","ids":null},'
      . '{"area":"modules","privilege":"all","ids":null},'
      . '{"area":"pages","privilege":"all","ids":null}]';

    $expectedFormerGroupRights = json_decode($formerGroupRightsJson);

    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg8-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg8-GROUP';

    $request = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $formerGroupRights = $responseData->rights;

    $this->assertInternalType('array', $formerGroupRights);
    foreach ($expectedFormerGroupRights as $rightZaehler => $expectedFormerGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $formerGroupRights);
      $expectedFormerGroupRightValues = get_object_vars($expectedFormerGroupRight);

      foreach ($expectedFormerGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $formerGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $formerGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }

    $alteredGroupRightsJson = '[{"area":"website","privilege":"publish","ids":null},'
      . '{"area":"templates","privilege":"all","ids":null},'
      . '{"area":"modules","privilege":"all","ids":null}]';

    $expectedAlteredGroupRights = json_decode($alteredGroupRightsJson);

    $rights = new \stdClass();
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":false,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $alteredGroupRights = $responseData->rights;
    $this->assertInternalType('array', $alteredGroupRights);
    foreach ($expectedAlteredGroupRights as $rightZaehler => $expectedAlteredGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $alteredGroupRights);
      $expectedAlterGroupRightValues = get_object_vars($expectedAlteredGroupRight);

      foreach ($expectedAlterGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $alteredGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $alteredGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }
  }

  /**
   * @test
   * @group integration
   */
  public function setPageRightsShouldSetEmptyPageRights()
  {
    $formerGroupRightsJson = '[{"area":"website","privilege":"publish","ids":null},'
      . '{"area":"templates","privilege":"all","ids":null},'
      . '{"area":"modules","privilege":"all","ids":null},'
      . '{"area":"pages","privilege":"edit","ids":["PAGE-12547dea-pob2-mk32-m111-92e7a4ba3703-PAGE","PAGE-0cf7e096-07b3-4ab5-895b-92e7a4ba3703-PAGE"]}]';

    $expectedFormerGroupRights = json_decode($formerGroupRightsJson);
    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg7-GROUP';

    $request = sprintf(
      '/group/getbyid/params/{"id":"%s","websiteId":"%s"}',
      $groupId,
      $websiteId
    );
    $this->dispatch($request);
    $response = $this->getResponseBody();

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
    $responseData = $response->getData();
    $formerGroupRights = $responseData->rights;

    $this->assertInternalType('array', $formerGroupRights);
    foreach ($expectedFormerGroupRights as $rightZaehler => $expectedFormerGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $formerGroupRights);
      $expectedFormerGroupRightValues = get_object_vars($expectedFormerGroupRight);

      foreach ($expectedFormerGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $formerGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $formerGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }

    $alteredGroupRightsJson = '[{"area":"website","privilege":"publish","ids":null},'
      . '{"area":"templates","privilege":"all","ids":null},'
      . '{"area":"modules","privilege":"all","ids":null}]';

    $expectedAlteredGroupRights = json_decode($alteredGroupRightsJson);

    $rights = new \stdClass();
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":false,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertTrue($response->getSuccess());

    $responseData = $response->getData();

    $alteredGroupRights = $responseData->rights;

    $this->assertInternalType('array', $alteredGroupRights);
    foreach ($expectedAlteredGroupRights as $rightZaehler => $expectedFormerGroupRight)
    {
      $this->assertArrayHasKey($rightZaehler, $alteredGroupRights);
      $expectedFormerGroupRightValues = get_object_vars($expectedFormerGroupRight);

      foreach ($expectedFormerGroupRightValues as $expectedValueKey => $expectedValue)
      {
        $this->assertObjectHasAttribute($expectedValueKey, $alteredGroupRights[$rightZaehler]);
        $this->assertSame($expectedValue, $alteredGroupRights[$rightZaehler]->$expectedValueKey);
      }
    }
  }

  /**
   * @test
   * @group integration
   * @dataProvider invalidRightsProvider
   */
  public function setPageRightsShouldReturnValidationErrorForInvalidRights($right)
  {
    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg7-GROUP';
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":false,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($right)
    );

    $this->dispatch($request);
    $response = $this->getResponseBody();

    $this->assertNotNull($response);
    $this->assertInternalType('string', $response);

    $response = new Response($response);
    $this->assertFalse($response->getSuccess());

    $responseError = $response->getError();
    $this->assertSame('rights', $responseError[0]->param->field);
  }

  /**
   * Standard-User darf keine Gruppe-Rechte setzen
   *
   * @test
   * @group integration
   */
  public function setPageRightsShouldReturnAccessDenied()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg8-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg8-GROUP';
    $rights = array(
      'PAGE-12547dea-own0-mk32-m111-92e7a4ba3703-PAGE' => array('edit')
    );
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":true,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );

    // User ohne Website-Zugehoerigkeit
    $this->assertSuccessfulLogin('access_rights_1@sbcms.de', 'seitenbau');

    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodyError($responseObject);
    $this->assertSame(7, $responseObject->error[0]->code);
  }

  /**
   * Super-User darf Gruppen-Rechte setzen
   *
   * @test
   * @group integration
   */
  public function superuserSetPageRightsShouldReturnSuccess()
  {
    $this->activateGroupCheck();

    $websiteId = 'SITE-edl54f03-nac4-4fdb-af34-72ebr0878rg8-SITE';
    $groupId = 'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg8-GROUP';
    $rights = array(
      'PAGE-12547dea-own0-mk32-m111-92e7a4ba3703-PAGE' => array('edit')
    );
    $request = sprintf(
      '/group/setpagerights/params/{"id":"%s","websiteId":"%s","allRights":true,"rights":%s}',
      $groupId,
      $websiteId,
      json_encode($rights)
    );

    $this->assertSuccessfulLogin('sbcms@seitenbau.com', 'seitenbau');
    $this->dispatch($request);

    $this->deactivateGroupCheck();

    $response = $this->getResponseBody();
    $responseObject = json_decode($response);
    $this->assertResponseBodySuccess($responseObject);
  }

  /**
   * @return array
   */
  public function invalidRightsProvider()
  {
    $stringRight = 'absbsbbs';
    $nonPageIdedRightGroup = array(
      'GROUP-edl54f03-nac4-4fdb-af34-72ebr0878rg7-GROUP' => array('edit')
    );
    $nonPageIdedRightModul = array(
      'MODUL-edl54f03-nac4-4fdb-af34-72ebr0878rg7-MODUL' => array('edit')
    );
    $nonExistingRightInSinglePage = array(
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-PAGE' => array('nonExistingRight')
    );
    $nonExistingRightInMultiplePages = array(
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-PAGE' => array('edit'),
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg6-PAGE' => array('nonExistingRight')
    );
    $emptyRightInSinglePage = array(
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-PAGE' => array()
    );
    $emptyRightInMultiplePages = array(
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg7-PAGE' => array('subAll'),
      'PAGE-edl54f03-nac4-4fdb-af34-72ebr0878rg8-PAGE' => array()
    );

    return array(
      array($stringRight),
      array($nonPageIdedRightGroup),
      array($nonPageIdedRightModul),
      array($nonExistingRightInSinglePage),
      array($nonExistingRightInMultiplePages),
      array($emptyRightInSinglePage),
      array($emptyRightInMultiplePages),
    );
  }
  /**
   * @return array
   */
  public function invalidGroupIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('TPL-0rap62te-0t4c-42c7-8628-f2cb4236eb45-TPL'),
    );
  }
  /**
   * @return array
   */
  public function invalidWebsiteIdsProvider()
  {
    return array(
      array(null),
      array(15),
      array('some_test_value'),
      array('GROUP-0rap62te-0t4c-42c7-8628-f2cb4236eb45-GROUP'),
    );
  }
}