<?php

// local config overrides
$localConfigFile = __DIR__.'/local.conf.php';
if (is_file($localConfigFile)) {
  /** @noinspection PhpIncludeInspection */
  include_once($localConfigFile);
}

define('CMS_PATH', realpath(__DIR__.'/../../../../../cms'));
define('APP_PATH', realpath(__DIR__.'/../../../../'));

defined('RENDER_LIB_PATH') || define('RENDER_LIB_PATH', realpath(APP_PATH.'/server/library/Render'));
defined('TEST_PATH') || define('TEST_PATH', realpath(__DIR__));
defined('MODULE_PATH') || define('MODULE_PATH', realpath(TEST_PATH.'/../../rz_core/modules/'));

echo 'Using APP_PATH: ' . APP_PATH . "\n";
echo 'Using RENDER_LIB_PATH: ' . RENDER_LIB_PATH . "\n";
echo 'Using TEST_PATH: ' . TEST_PATH . "\n";
echo 'Using MODULE_PATH: ' . MODULE_PATH . "\n";

require_once(RENDER_LIB_PATH.'/ModuleInterface.php');
require_once(RENDER_LIB_PATH.'/Unit.php');
require_once(RENDER_LIB_PATH.'/ModuleInfo.php');
require_once(RENDER_LIB_PATH.'/APIs/APIv1/HeadAPI.php');
require_once(RENDER_LIB_PATH.'/APIs/APIv1/CSSAPI.php');
require_once(RENDER_LIB_PATH.'/APIs/APIv1/RenderAPI.php');
require_once(RENDER_LIB_PATH.'/APIs/APIv1/WebsiteSettingsNotFound.php');
require_once(RENDER_LIB_PATH.'/APIs/RootAPIv1/RootCssAPI.php');
require_once(RENDER_LIB_PATH.'/APIs/RootAPIv1/RootRenderAPI.php');
require_once(RENDER_LIB_PATH.'/InfoStorage/ModuleInfoStorage/IModuleInfoStorage.php');

// backend apis for navigation
require_once(RENDER_LIB_PATH.'/APIs/APIv1/Navigation.php');
require_once(RENDER_LIB_PATH.'/APIs/APIv1/Page.php');

$pageUrlHelperPath = RENDER_LIB_PATH.'/PageUrlHelper';
require_once($pageUrlHelperPath . '/PageUrlNotAvailable.php');
require_once($pageUrlHelperPath . '/IPageUrlHelper.php');
require_once($pageUrlHelperPath . '/AbstractPageUrlHelper.php');
require_once($pageUrlHelperPath . '/SimplePageUrlHelper.php');

$navInfoStoragePath = RENDER_LIB_PATH . '/InfoStorage/NavigationInfoStorage';
require_once($navInfoStoragePath.'/INavigationInfoStorage.php');
require_once($navInfoStoragePath.'/AbstractNavigationInfoStorage.php');
require_once($navInfoStoragePath.'/NavigationInfoStorageItem.php');
require_once($navInfoStoragePath.'/ArrayBasedNavigationInfoStorage.php');
require_once($navInfoStoragePath.'/Exceptions/NavigationInfoStorageItemDoesNotExists.php');


require_once(TEST_PATH.'/Helper/Test/Rukzuk/HelperUtils.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/GetSetMock.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/CssApiMock.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/RenderApiMock.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/ModuleTestCase.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/CssTestCase.php');
require_once(TEST_PATH.'/Helper/Test/Rukzuk/TestCaseException.php');

