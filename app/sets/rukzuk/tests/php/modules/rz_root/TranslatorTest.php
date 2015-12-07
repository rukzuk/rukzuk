<?php

use \Test\Rukzuk\ModuleTestCase;
use \Rukzuk\Modules\Translator;

class TranslatorTest extends ModuleTestCase
{
  protected $customData = array(
    'i18n' => array(
      'foo' => array('en' => 'foo-en', 'de' => 'foo-de'),
      'bar' => array('en' => 'bar-en', 'de' => 'bar-de'),
      'baz' => array('en' => 'baz-en', 'de' => 'baz-de')
    )
  );

  public function testI18n()
  {
    // prepare
    $api = $this->createRenderApi();
    $moduleInfo = $this->createModuleInfo();
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    $this->assertNull($translator->i18n('fooBarBaz'));
    $this->assertEquals($this->customData['i18n']['foo'], $translator->i18n('foo'));
    $this->assertEquals($this->customData['i18n']['bar'], $translator->i18n('bar'));
    $this->assertEquals($this->customData['i18n']['baz'], $translator->i18n('baz'));
  }

  public function testI18n_noCustomData()
  {
    // prepare
    $api = $this->createRenderApi();
    $moduleInfo = $this->createModuleInfo(array(
      'custom' => null
    ));
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    $this->assertNull($translator->i18n('fooBarBaz'));
    $this->assertNull($translator->i18n('foo'));
    $this->assertNull($translator->i18n('bar'));
    $this->assertNull($translator->i18n('baz'));
  }

  public function testTranslate()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'interfaceLocale' => 'de'
    ));
    $moduleInfo = $this->createModuleInfo();
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    $this->assertEquals($this->customData['i18n']['foo']['de'], $translator->translate('foo'));
    $this->assertEquals($this->customData['i18n']['bar']['de'], $translator->translate('bar'));
    $this->assertEquals($this->customData['i18n']['baz']['de'], $translator->translate('baz'));
  }

  public function testTranslate_unknownLanguage()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'interfaceLocale' => 'it'
    ));
    $moduleInfo = $this->createModuleInfo();
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    $this->assertEquals($this->customData['i18n']['foo']['en'], $translator->translate('foo'));
    $this->assertEquals($this->customData['i18n']['bar']['en'], $translator->translate('bar'));
    $this->assertEquals($this->customData['i18n']['baz']['en'], $translator->translate('baz'));
  }

  public function testTranslate_unknownKey()
  {
    // prepare
    $api = $this->createRenderApi();
    $moduleInfo = $this->createModuleInfo();
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    $this->assertEquals('fooBarBaz', $translator->translate('fooBarBaz'));
    $this->assertEquals('bazBarForr', $translator->translate('fooBarBaz', 'bazBarForr'));
  }


  public function testTranslateInput()
  {
    // prepare
    $api = $this->createRenderApi(array(
      'interfaceLocale' => 'de'
    ));
    $moduleInfo = $this->createModuleInfo();
    $translator = new Translator($api, $moduleInfo);
    // execute/verify
    // fallback to en
    $this->assertEquals('this is en', $translator->translateInput('{"en": "this is en", "ch": "test"}'));
    // current lang
    $this->assertEquals('test', $translator->translateInput('{"en": "this is en", "de": "test"}'));
    // fallback to string (malformed json)
    $this->assertEquals('{"en": "this is en", "de": "test}', $translator->translateInput('{"en": "this is en", "de": "test}'));
    // first key (if not current lang and not en) - NOTE: there is no order so we just check that the json is unwrapped
    $this->assertEquals('this', $translator->translateInput('{"pl": "this", "ch": "this"}'));
  }
}

