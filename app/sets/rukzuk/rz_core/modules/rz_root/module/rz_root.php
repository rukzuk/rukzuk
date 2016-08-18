<?php
namespace Rukzuk\Modules;


// require default module implementation
use Rukzuk\Modules\Lib\DynCSSEngine;

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/lib/Mailer.php');
require_once(dirname(__FILE__) . "/lib/ChildModuleDependency.php");
require_once(dirname(__FILE__) . '/lib/SimpleModule.php');


/**
 * Class rz_root
 * The rukzuk root module
 * @package Rukzuk\Modules
 */
class rz_root extends SimpleModule
{

  /**
   * Root Modules css function (called by Backend Visitor)
   * @param \Render\APIs\RootAPIv1\RootCssAPI $rootCssApi
   * @param \Render\Unit $rootUnit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function css($rootCssApi, $rootUnit, $moduleInfo)
  {
    // Skip this in Edit Mode, as we need to
    // generate some boilerplate for the client-side CSS implementation
    if (!$rootCssApi->isEditMode()) {
      $dynCss = $this->getDynCSSLib($moduleInfo);
      $dynCss->generateCSS($rootCssApi, $rootUnit, false);
    }
  }

  /**
   * Root Modules Render Method (called by Backend Visitor)
   * @param \Render\APIs\RootAPIv1\RootRenderAPI $rootApi
   * @param \Render\Unit $rootUnit
   * @param \Render\ModuleInfo $moduleInfo
   */
  public function render($rootApi, $rootUnit, $moduleInfo)
  {
    // lang/locale
    $pageLang = $rootApi->getFormValue($rootUnit, 'lang', 'en-US');
    $pageLangLowDash = str_replace('-', '_', $pageLang);
    setlocale(LC_ALL, $pageLangLowDash.'.UTF8', $pageLang);
    $rootApi->setLocale($pageLangLowDash);

    // read custom data provided by module implementations (per module and per unit)
    $moduleData = $rootApi->getAllModuleData($rootUnit);
    $unitData = $rootApi->getAllUnitData($rootUnit);
    $nav = $rootApi->getNavigation();

    // http header redirect (only in live and preview mode)
    if ($rootApi->isLiveMode() || $rootApi->isPreviewMode()) {
      foreach ($unitData as $d) {
        $redirect = isset($d['redirect']) ? $d['redirect'] : null;
        if (is_array($redirect) && array_key_exists('url', $redirect)) {
          header('Location: ' . $redirect['url'], true);
          exit;
        }
      }

      $currentPage = $nav->getPage($nav->getCurrentPageId());
      $pageTitle = $currentPage->getTitle();
      $pageAttributes = $currentPage->getPageAttributes();
      $redirectFirstChild = false;
      if (array_key_exists('enableRedirect', $pageAttributes)) {
        if ($pageAttributes['enableRedirect'] == 1) {
          if ($pageAttributes['redirectType'] == "firstChild") {
            $redirectFirstChild = true;
          } else if ($pageAttributes['redirectType'] == "page") {
            if (array_key_exists('redirectPageId', $pageAttributes)) {
              $redirectPageId = $pageAttributes['redirectPageId'];
              if (($redirectPageId != '') && ($redirectPageId != $nav->getCurrentPageId())) {
                $redirectUrl = $nav->getPage($redirectPageId)->getUrl();
                if ($redirectUrl != '') {
                  header('Location: ' . $redirectUrl);
                  exit();
                }
              }
            }
          } else {
            if (array_key_exists('redirectUrl', $pageAttributes)) {
              $redirectUrl = $pageAttributes['redirectUrl'];
              if (preg_match("/^[http]/", $redirectUrl)) {
                if ($rootApi->isLiveMode()) {
                  header('Location: ' . $redirectUrl);
                  exit();
                } else {
                  $i18n = new Translator($rootApi, $moduleInfo);
                  $msg1 = $i18n->translate('error.redirectLiveOnly1');
                  $msg2 = $i18n->translate('error.redirectLiveOnly2');
                  echo '<html><body style="margin:0;background-color: #454444;font-family:\'Trebuchet MS\',sans-serif;font-size:20px;color: #ffffff;">';
                  echo '<div style="display:flex;height:100vh;width:80%;padding:0 10%;justify-content:center;align-items:center;text-align:center;">'.$msg1.$redirectUrl.$msg2.'</div>';
                  echo '</body></html>';
                  exit();
                }
              }
            }
          }

        }
      }

      // redirect to first child page if page title equal [redirect]
      if ((strtolower($pageTitle) == '[weiterleiten]') || (strtolower($pageTitle) == '[redirect]') || $redirectFirstChild) {

        $childrenIds = $nav->getChildrenIds($nav->getCurrentPageId());
        if (count($childrenIds)) {

          $currentPage = $nav->getPage($nav->getCurrentPageId());
          $curUrl      = $currentPage->getUrl();

          $fstChild    = $nav->getPage($childrenIds[0]);
          $url         = $fstChild->getUrl();

          if(!empty($url) && $url!=$curUrl) {
            header('Location: ' . $url);
            exit();
          }
        }
      }
    }

    // protected pages
    $password_protection = $rootApi->getWebsiteSettings('password_protection');
    if ($password_protection['enableProtectedPage'] && $rootApi->isPage()) {
      if ($rootApi->isLiveMode() || ($password_protection['inPreviewMode'] && $rootApi->isPreviewMode())) {
        $currentPageId = $nav->getCurrentPageId();
        $protectedPage = $password_protection['protectedPage'];
        $parentPages = $nav->getNavigatorIds($currentPageId);
        if (in_array($protectedPage, $parentPages) || ($protectedPage == '')) {
          $validLogin = $this->authenticate($password_protection['loginPasswords']);
          if (!$validLogin) {
            exit;
          }
        }
      }

    }


    // error reporting
    if ($rootApi->isEditMode() && $rootApi->getFormValue($rootUnit, 'debugShowPhpErrors')) {
      error_reporting(E_ALL);
      ini_set('display_errors', true);
    }

    // session TODO: remove?
    if (!$rootApi->isEditMode()) {
      if ($rootApi->getFormValue($rootUnit, 'enablePHPSession') && !isset($_SESSION)) {
        session_start();
      }
    }

    // start output
    echo '<!DOCTYPE html>';
    echo "<html lang='{$pageLang}'>";
    echo '<head>';

    // static meta tags
    echo '<meta charset="utf-8">';
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    echo '<meta name="viewport" content="initial-scale=1">';

    // favicon and appleTouchIcon
    $this->insertFavicon($rootApi, $rootUnit);

    // title tag and meta description
    $this->insertTitleAndDescription($rootApi, $rootUnit);

    // JS API
    echo $rootApi->isEditMode() ? '<script src="' . $rootApi->getJsApiUrl() . '"></script>' : '';

    // srcset polyfill (load before modernizer because of the htmlshiv, load before CSS according to doc)
    // http://caniuse.com/#feat=srcset (Safari, Android 4.x, Firefox)
    echo <<<EOF
    <script>
    if(!window.HTMLPictureElement){
      document.createElement('picture');
      function loadJS(u){var r = document.getElementsByTagName( "script" )[ 0 ], s = document.createElement( "script" );s.src = u;r.parentNode.insertBefore( s, r );}
      loadJS('{$moduleInfo->getAssetUrl('js/respimage.js')}');
    }
    </script>
EOF;

    // CSS Assets (static css files via <link>)
    $this->insertCssAssets($moduleData);

    // Dynamic CSS (only in edit mode - see css for live mode)
    if ($rootApi->isEditMode()) {
      $dynCss = $this->getDynCSSLib($moduleInfo);
      $dynCss->generateCSS($rootApi, $rootUnit, true);

      // Selectors Data (TODO: this could be also accessed by CMS.getCustomData(moduleId).selectors ?)
      echo '<script type="text/x-text-json" id="available_selectors">';
      echo $this->insertSelectorsJson($moduleData);
      echo '</script>';
    } else {
      echo '<link rel="stylesheet" type="text/css" href="'.$rootApi->getCssUrl().'">';
    }

    //
    // JS
    //

    // responsive images
    if (!$rootApi->isEditMode()) {
      echo "<script>window.lazySizesConfig = { preloadAfterLoad: !(/mobi/i.test(navigator.userAgent)) };</script>";
    }
    echo "<script src='{$moduleInfo->getAssetUrl('js/lazysizes.js')}' async=''></script>";
    echo "<script src='{$moduleInfo->getAssetUrl('js/ls.progressive.js')}' async=''></script>";

    // modernizer for feature detection (and htmlshiv)
    echo "<script src='{$moduleInfo->getAssetUrl('js/modernizer.js')}'></script>";

    // insert jquery here, so it's already available in body
    echo "<script src='{$moduleInfo->getAssetUrl('js/jquery-2.1.4.js')}'></script>";

    // HTML/CSS polyfills
    if (!$rootApi->isEditMode()) {
      // vw/vh css units (http://caniuse.com/#feat=viewport-units)
      // Polyfill "prefixfree.viewport-units": modified to support browser resize
      echo "<script>Modernizr.load({test: Modernizr.cssvwunit && Modernizr.cssvhunit, nope: ['{$moduleInfo->getAssetUrl('js/prefixfree.stylefix.js')}', '{$moduleInfo->getAssetUrl('js/prefixfree.viewport-units.js')}']});</script>";
    }

    // HTML HEAD HOOK (provided by children)
    $this->insertHtmlHead($moduleData, $unitData);

    echo '</head>';

    // Body
    $rootClasses = array();
    if ($rootApi->isTemplate()) {
      $rootClasses[] = 'isTemplate';
    }
    echo '<body id="' . $rootUnit->getId() . '" class="' . implode(' ', $rootClasses) . '">';

    // CONTENT (child modules of the root module)
    $rootApi->renderChildren($rootUnit);

    // JS Assets (load after DOM)
    $this->insertJsAssets($moduleData);
    $this->insertJsModules($moduleData, $moduleInfo, $rootApi->isEditMode());

    // HTML BOTTOM HOOK (provided by children)
    $this->insertHtmlBottom($moduleData);

    // CSS Error Console
    if (isset($dynCss) && $rootApi->isEditMode() && $rootApi->getFormValue($rootUnit, 'debugShowDynCssErrors')) {
      $this->insertCssErrorPanel($dynCss->getErrors());
    }

    echo '</body>';
    echo '</html>';
  }

  /////////////////////////////////////////////////////////////////////////////
  //
  // helpers (all private)
  //
  //

  /**
   * Loads the DynCSS helper if needed
   * NOTE: This should never be called on a published page! (as it requires v8js php-ext)
   * @param \Render\ModuleInfo $moduleInfo
   * @return Lib\DynCSS
   */
  private function getDynCSSLib($moduleInfo) {
    require_once(dirname(__FILE__) . '/lib/dyncss/DynCSS.php');
    return new Lib\DynCSS(new DynCSSEngine($moduleInfo->getAssetPath()));
  }

  /**
   * Inserts the code for the favicon (as well as touch icons)
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @param \Render\Unit $unit
   */
  private function insertFavicon($api, $unit)
  {
    $favicon = $api->getFormValue($unit, 'favicon');
    $appleTouchIcon = $api->getFormValue($unit, 'appleTouchIcon');

    if ($favicon) {
      try {
        echo HtmlTagBuilder::link()->set(array(
          'rel' => 'shortcut icon',
          'href' => $api->getMediaItem($favicon)->getUrl(),
          'type' => 'image/x-icon'
        ));
      } catch(\Exception $e) {}
    }

    if ($appleTouchIcon) {
      try {
        $touchIcon = $api->getMediaItem($appleTouchIcon)->getImage();
        $touchIcon->resizeScale(144, 144);
        $touchIconUrl = $touchIcon->getUrl();

        echo HtmlTagBuilder::link()->set(array(
          'rel' => 'apple-touch-icon',
          'href' => $touchIconUrl
        ));

        echo HtmlTagBuilder::link()->set(array(
          'rel' => 'icon',
          'href' => $touchIconUrl
        ));
      } catch(\Exception $e) {}
    }
  }

  /**
   * Title and Description
   * @param \Render\APIs\RootAPIv1\RootRenderAPI $rootApi
   * @param \Render\Unit $rootUnit
   */
  private function insertTitleAndDescription($rootApi, $rootUnit)
  {
    // title
    $nav = $rootApi->getNavigation();
    $currentPage = $nav->getPage($nav->getCurrentPageId());
    $pageTitle = $currentPage->getTitle();
    $title = $rootApi->getFormValue($rootUnit, 'titlePrefix', '') . $pageTitle . $rootApi->getFormValue($rootUnit, 'titleSuffix', '');
    echo HtmlTagBuilder::title($title);

    // description
    $description = $currentPage->getDescription();
    if ($description != '') {
      echo HtmlTagBuilder::meta()->set(array('name' => 'description', 'content' => $description));
    }
  }

  public function insertCssAssets($moduleData)
  {
    foreach ($moduleData as $moduleId => $data) {
      if (isset($data['cssAssets'])) {
        foreach ($data['cssAssets'] as $cssAssets) {
          echo HtmlTagBuilder::link()->set(array('rel' => 'stylesheet', 'href' => $cssAssets));
        }
      }
    }
  }

  public function insertHtmlHead($moduleData, $unitData)
  {
    foreach ($moduleData as $moduleId => $data) {
      $val = $this->getValue('htmlHead', $data, '');
      if ($val != '') {
        echo "<!-- HEAD for " . $moduleId . " -->";
        echo $val;
      }
    }

    foreach ($unitData as $unitId => $data) {
      $val = $this->getValue('htmlHead', $data, '');
      if ($val != '') {
        echo "<!-- HEAD for UNIT " . $unitId . " -->";
        echo $val;
      }
    }
  }

  public function insertHtmlBottom($moduleData)
  {
    foreach ($moduleData as $moduleId => $data) {
      $val = $this->getValue('htmlBottom', $data, '');
      if ($val != '') {
        echo "<!-- BOTTOM for " . $moduleId . " -->";
        echo $val;
      }
    }
  }

  public function insertSelectorsJson($moduleData)
  {
    $result = array();
    foreach ($moduleData as $moduleId => $data) {
      if (!empty($data['selectors'])) {
        $result[$moduleId] = $data['selectors'];
      }
    }

    echo json_encode($result);
  }

  public function insertJsAssets($moduleData)
  {
    foreach ($moduleData as $moduleId => $data) {
      if (isset($data['jsScripts'])) {
        foreach ($data['jsScripts'] as $script) {
          echo '<script src="' . $script . '" data-module-id="' . $moduleId . '"></script>';
        }
      }
    }
  }

  public function insertJsModules($moduleData, $moduleInfo, $isEditMode)
  {
    $loadCode = '';
    $paths = array();

    foreach ($moduleData as $moduleId => $data) {
      if (is_array($data['jsModulePaths'])) {
        $paths = array_replace($paths, $data['jsModulePaths']);
      }

      if (isset($data['jsModules'])) {
        // i18n is currently only supported/needed in edit mode
        $i18n = '';
        if ($isEditMode) {
          $i18nJsonStr = json_encode($data['i18n']);
          $i18n = " , i18n: '".str_replace("'", "\\'", $i18nJsonStr)."'";
        }
        $assetUrl = $this->getValue('assetUrl', $data, '');

        foreach ($data['jsModules'] as $scriptAssetUrl) {
          $loadCode .= 'require([\''.$scriptAssetUrl.'\'], function (moduleScript) {';
          $loadCode .= 'if (moduleScript && typeof moduleScript.init === \'function\') {';
          $loadCode .= 'moduleScript.init({ moduleId: \''.$moduleId.'\', assetUrl: \''.$assetUrl.'\''.$i18n.' });';
          $loadCode .= '}});';
        }
      }
    }

    if (!empty($paths)) {
      $reqCfg  = 'require.config({paths: {';
      forEach ($paths as $key => $path) {
        $reqCfg .= "'$key': '$path',";
      }
      $reqCfg .= '}});';
      $loadCode =  $reqCfg . $loadCode;
    }


    if ($loadCode !== '') {
      // load require.js only if modules are present, also provide global libs in require.js modules
      echo "<script src='{$moduleInfo->getAssetUrl('js/require.js')}'></script>";
      echo "<script src='{$moduleInfo->getAssetUrl('js/require-stubs.js')}'></script>";
      // load code
      echo '<script class="requirejs-modules">';
      echo $loadCode;
      echo '</script>';
    }

  }

  private function insertCssErrorPanel($errors)
  {
      if (count($errors) > 0) {
        echo '<pre class="dyncssErrorConsole">';
        foreach ($errors as $err) {
          echo $err . "\n\n";
        }
        echo '</pre>';
      }
  }


  //
  // This file implements the authentication using
  // HTTP digest algorithm.
  // just include it on you php file and call authenticate();
  // written by Jader Feijo (jader@movinpixel.com)
  //

  // function to parse the http auth header
  public function http_digest_parse($txt)
  {
    $keys_arr = array();
    $values_arr = array();
    $cindex = 0;
    $parts = explode(',', $txt);

    foreach($parts as $p) {
      $p = trim($p);
      $kvpair = explode('=', $p);
      $kvpair[1] = str_replace("\"", "", $kvpair[1]);
      $keys_arr[$cindex] = $kvpair[0];
      $values_arr[$cindex] = $kvpair[1];
      $cindex++;
    }

    $ret_arr = array_combine($keys_arr, $values_arr);
    $ret_arr['uri'] = $_SERVER['REQUEST_URI'];
    return $ret_arr;
  }

  public function get_user_password($username, $loginAndPasswords) {
    // return the password for the given username
    if ($username == '') {
      return false;
    }
    $loginAndPasswords = explode("\n", $loginAndPasswords);
    foreach ($loginAndPasswords as &$loginAndPassword) {
      $loginAndPassword = explode(":", $loginAndPassword);
      if (($loginAndPassword[0] == $username) && ($loginAndPassword[1] != '') && ($loginAndPassword[0] != '')) {
        return $loginAndPassword[1];
      }
    }
    return false;
  }

  public function authenticate($loginAndPasswords) {

    $realm = "";
    $header1 = 'WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm). '"';
    $header2 = 'HTTP/1.0 401 Unauthorized';

    if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
      header($header1);
      header($header2);
    }

    // analyze the PHP_AUTH_DIGEST variable
    $data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
    $password = $this->get_user_password($data['username'], $loginAndPasswords);


    if (!$password) {
      header($header1);
      header($header2);
      return FALSE;
    }

    if (!$data || $password == -1) {
      header($header1);
      header($header2);
      return FALSE;
    }

    // generate the valid response
    $A1 = md5($data['username'] . ':' . $realm . ':' . $password);
    $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
    $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

    if ($data['response'] != $valid_response) {
      header($header1);
      header($header2);
      return FALSE;
    }

    return TRUE;
  }



}
