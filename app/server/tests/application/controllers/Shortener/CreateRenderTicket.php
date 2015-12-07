<?php
namespace Application\Shortener\Create;

use Test\Seitenbau\ControllerTestCase,
    Test\Seitenbau\Cms\Response as Response;

/**
 * ShortenerController CreateRenderTicket Test
 *
 * @package      Test
 * @subpackage   Controller
 */

class CreateRenderTicketTest extends ControllerTestCase
{
  private $websiteId = 'SITE-renderer-site-test-1234-mjsncgmjszt1-SITE';

  protected function setUp()
  {
    parent::setUp();

    $this->activateGroupCheck();
  }

  protected function tearDown()
  {
    $this->deactivateGroupCheck();

    parent::tearDown();
  }

  /**
   * @test
   * @group integration
   * @dataProvider createTicketIdProvider
   */
  public function successCreateRenderTicket($params)
  {
    $this->assertSuccessfulLogin('access_rights_2@sbcms.de', 'seitenbau');
    
    $request = $this->createRequestUrl('Shortener', 'createRenderTicket', $params);

    $this->dispatch($request);
    $response = $this->getResponseBody();
    
    $response = new Response($response);
    $this->assertTrue($response->getSuccess());
  }

  /**
   * @return array
   */
  public function createTicketIdProvider()
  {
    return array(
      array(array(
        'websiteId' => $this->websiteId,
        'type' => 'page',
        'id' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE',
        'protect' => true,
        'credentials' => array(
          'username' => 'benutzer',
          'password' => 'passwort'
        ),
        'ticketLifetime' => 60*5, // 5 Minuten
        'sessionLifetime' => 60*30, // 30 Minuten
        'remainingCalls' => 999
      )),
      array(array(
        'websiteId' => $this->websiteId,
        'type' => 'page',
        'id' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE',
        'protect' => true
      )),
      array(array(
        'websiteId' => $this->websiteId,
        'type' => 'page',
        'id' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE',
        'protect' => false
      )),
      array(array(
        'websiteId' => $this->websiteId,
        'type' => 'page',
        'id' => 'PAGE-renderer-page-1472-47du-m7j9klswmc71-PAGE',
      )),
    );
  }
}