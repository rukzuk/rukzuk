<?php
namespace Cms;

use Seitenbau\Registry,
    Cms\Feedback;
use Test\Rukzuk\AbstractTestCase;
use Test\Rukzuk\ConfigHelper;

/**
 * Feedback Test
 *
 * @package      Cms
 */

class FeedbackTest extends AbstractTestCase
{
  const BACKUP_CONFIG = true;


  protected function setUp()
  {
    parent::setUp();
  }

  protected function tearDown()
  {
    parent::tearDown();
  }

  /**
   * Ordnungsgemaesse Initialisierung eines in der Config angegebenen Adapters
   *
   * @test
   * @group library
   */
  public function constructInitConfigAdapter()
  {
    ConfigHelper::mergeIntoConfig(array('feedback' => array('adapter' => 'mail')));

    $feedback = new Feedback(Registry::getConfig()->feedback);

    $this->assertInstanceOf('\Cms\Feedback\Adapter\Mail', $feedback->getAdapter());
  }

  /**
   * Ist kein Adapter in der Config angegeben, so soll der Default-Adapter
   * genutzt werden
   *
   * @test
   * @group library
   */
  public function constructInitDefaultAdapter()
  {
    ConfigHelper::removeValue(array('feedback', 'adapter'));

    $config = Registry::getConfig()->feedback;

    $feedback = new Feedback(Registry::getConfig()->feedback);

    // Default-Adapter i.d.R. unbekannt -> Pruefung lediglich auf Typ Adapter\Base
    $this->assertInstanceOf('\Cms\Feedback\Adapter\Base', $feedback->getAdapter());
  }

  /**
   * angegebener Adapter in der Config kann nicht initialisiert werden
   *
   * @test
   * @group library
   * @expectedException \Cms\Feedback\Exception
   */
  public function constructInitAdapterFailed()
  {
    ConfigHelper::mergeIntoConfig(array('feedback' => array('adapter' => 'gibtesnicht123')));

    $feedback = new Feedback(Registry::getConfig()->feedback);
  }
}