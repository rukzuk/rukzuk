<?php
namespace Test\Rukzuk;

/**
 * Class V8JSAbsurdJSTest
 * @package Test\Rukzuk
 */
class V8JSAbsurdJSTest extends  \PHPUnit_Framework_TestCase
{

  public function testV8EngineAvailable() {
    new \V8Js('PHP', array(), array());
  }

  public function testAbsurdJSCompile() {
    $vm = new \V8Js('PHP', array(), array());

    // not using extensions as the error reporting is better on executed strings
    // prefixing the code changes the lines, but we need to do
    // this as most js scripts are not able to detect v8js and assume a browser
    $code = file_get_contents(MODULE_PATH.'/rz_root/module/lib/dyncss/js/browserEmulator.js');
    $code .= file_get_contents(MODULE_PATH.'/rz_root/assets/notlive/dyncss/absurd.js');

    $vm->executeString($code, 'absurdjs_TEST');
  }



  public function testDynCSS() {
    $vm = new \V8Js('PHP', array(), array());

    \V8Js::registerExtension('browser_test', file_get_contents(MODULE_PATH.'/rz_root/module/lib/dyncss/js/browserEmulator.js'));
    \V8Js::registerExtension('absurdhat_test', file_get_contents(MODULE_PATH.'/rz_root/assets/notlive/dyncss/absurdhat.js'));
    \V8Js::registerExtension('absurd_test', file_get_contents(MODULE_PATH.'/rz_root/assets/notlive/dyncss/absurd.js'));


    $vm = new \V8Js('PHP', array(), array('browser_test', 'absurdhat_test', 'absurd_test'));
    $code = file_get_contents(MODULE_PATH.'/rz_root/assets/notlive/dyncss/dyncss.js');

    $vm->executeString($code, 'dyncss_TEST');
  }

}

