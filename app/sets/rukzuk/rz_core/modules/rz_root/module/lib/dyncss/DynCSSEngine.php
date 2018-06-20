<?php
namespace Rukzuk\Modules\Lib;

/**
 * Class DynCSSEngine
 *
 * @requires php-v8js extension
 * @package Rukzuk\Modules\Lib
 */
class DynCSSEngine {

  private $basePath = '';
  private $assetPath = '';
  private $vm = null;
  private $loadedPlugins = array();


  public function __construct($assetPath) {
    // path for php specific glue js files
    $this->basePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
    // path for with shared js files
    $this->assetPath = $assetPath . DIRECTORY_SEPARATOR . 'notlive' . DIRECTORY_SEPARATOR . 'dyncss' . DIRECTORY_SEPARATOR;
  }

  /**
   * JS VM
   * @return \V8Js
   * @throws JSV8AppException
   */
  protected function getVM() {
    if (is_null($this->vm)) {
      // cache buster
      $version = rand();

      // disable deprecation warning since php-v8js 2.0 (as there is no working alternative to the extension system right now)
      // \V8Js::registerExtension and the $extensions array are both deprecated
      $errLevel = error_reporting();
      error_reporting($errLevel & ~E_DEPRECATED);

      // emulate a (very poor) browser
      \V8Js::registerExtension('browserEmulator'.$version, file_get_contents($this->basePath . 'browserEmulator.js'));
      // load absurd.js browser version (shared with client)
      \V8Js::registerExtension('absurd'.$version, file_get_contents($this->assetPath . 'absurd.js'));
      \V8Js::registerExtension('absurdHat'.$version, file_get_contents($this->assetPath . 'absurdhat.js'));

      // load generic dyncss implementation
      \V8Js::registerExtension('dyncss'.$version, file_get_contents($this->assetPath . 'dyncss.js'));

      // create variable mapping
      $extensions = array('browserEmulator'.$version, 'absurd'.$version, 'absurdHat'.$version, 'dyncss'.$version);

      // start engine (discard any output via print)
      ob_start();
      $vm = new \V8Js('PHP', array(), $extensions);
      ob_end_clean();

      // add error handler
      $vm->error = function ($error) {
        throw new JSV8AppException($error);
      };

      $this->vm = $vm;

      // back to the regular error level (whatever it was)
      error_reporting($errLevel);

    }
    return $this->vm;
  }


  /**
   * Compile CSS using absurd.js (wrapped in dyncss.js)
   * @param array $moduleCssData
   * @param $jsonTree
   * @param $formValues
   * @param \Render\APIs\APIv1\CSSAPI $api
   * @return string
   */
  public function compile($moduleCssData, $jsonTree, $formValues, $api) {

    $vm = $this->getVM();

    // data
    $vm->jsonTree = $jsonTree;
    $vm->resolutions = $api->getResolutions();
    $vm->isEditMode = $api->isEditMode();

    // functions
    $compiledCss = '';
    $vm->callback = function ($result) use (&$compiledCss) {
      $compiledCss = $result;
    };

    $vm->getFormValues = function ($unitId) use(&$formValues) {
      if (isset($formValues[$unitId])) {
        return $formValues[$unitId];
      }
      return array();
    };

    $vm->getColorById = function ($colorId) use (&$api) {
      $color = $api->getColorById($colorId);
      return $color;
    };

    $vm->getImageUrl = function ($mediaId, $width, $quality) use (&$api) {
      if (!$mediaId || $mediaId === 'null') {
        return null;
      }

      try {
        $mediaItem = $api->getMediaItem($mediaId);
      } catch(\Exception $e) {
        // media item not found
        return null;
      }

      try {
        $img = $mediaItem->getImage();

        if ($width > 0) {
          $img->resizeScale($width);
        }

        if (is_numeric($quality) && !is_nan($quality)) {
          $img->setQuality($quality);
        }
        return $img->getUrl();
      } catch(\Exception $e) {
        // use just the url (svg for example)
        return $mediaItem->getUrl();
      }

    };

    $vm->getMediaUrl = function ($mediaId, $download = false) use (&$api) {
      if (!$mediaId || $mediaId === 'null') {
        return null;
      }

      try {
        $mediaItem = $api->getMediaItem($mediaId);

        if ($download) {
          return $mediaItem->getDownloadUrl();
        } else {
          return $mediaItem->getUrl();
        }
      } catch (\Exception $e) {
        return null;
      }
    };


    // run module code (only once per file)
    foreach ($moduleCssData as $data) {
      $path = $data['path'];
      if (!isset($this->loadedPlugins[$path])) {
        $this->loadedPlugins[$path] = true;
        $vm->executeString(file_get_contents($path), $path, \V8Js::FLAG_FORCE_ARRAY);
      }
    }

    // run dyncss compiler
    $dynCssBackendJsPath = $this->basePath . 'dyncssBackend.js';
    $vm->executeString(file_get_contents($dynCssBackendJsPath), $dynCssBackendJsPath, \V8Js::FLAG_FORCE_ARRAY);

    return $compiledCss;
  }

}

/**
 * Class JSV8AppException
 * Application error in JS code executed via php-v8js
 * @package Rukzuk\Modules\DynCSSEngine
 */
class JSV8AppException extends \Exception {

}
