<?php
namespace Cms\Business\Cli;

use Leafo\ScssPhp\Compiler as ScssCompiler;
use \Seitenbau\FileSystem as FS;
use \Cms\ExceptionStack as CmsExceptionStack;

/**
 * Class SassTheme
 * @package Cms\Business\Cli
 */
class SassTheme
{

  /**
   * @var array
   */
  private $requiredBrandingVars = [];

  /**
   * @var string file system path to the scss files
   */
  private $sourcePath;

  /**
   * @var string file system path to the target css theme dir
   */
  private $targetPath;

  /**
   * @var array mapping of scss to css files (source => target) files names only
   */
  private $themeFileNames;

  public function __construct($sourcePath, $targetPath)
  {
    $this->sourcePath = $sourcePath;
    $this->targetPath = $targetPath;

    // scss files which will be compiled for theming
    $this->themeFileNames = [
      'login-theme.scss' => 'login-theme.css',
      'cms-theme.scss' => 'cms-theme.css'
    ];

    // branding vars and their encode function
    $this->requiredBrandingVars = [
      'color' => function ($c) {
        return $c;
      },
      'logo' => function ($l) {
        return '"' . $l . '"';
      }
    ];
  }

  /**
   * @param array $rawThemeVars
   *
   * @return array
   */
  protected function determineThemeVars(array $rawThemeVars)
  {
    $themeVars = [];
    foreach ($this->requiredBrandingVars as $key => $encodeFunc) {
      if (isset($rawThemeVars[$key])) {
        $value = $rawThemeVars[$key];
        if ($encodeFunc && is_callable($encodeFunc)) {
          $value = $encodeFunc($value);
        }
        $themeVars[$key] = $value;
      }
    }
    return $themeVars;
  }

  /**
   * Encode array as scss variables
   * @param array $themeVars
   * @param string $prefix - prefix
   * @return string - scss code
   */
  protected function convertVarsToScssCode($themeVars, $prefix = 'brand-')
  {
    $sassBrandingVars = '';
    foreach ($themeVars as $key => $value) {
      $sassBrandingVars .= "\$$prefix$key: $value;\n";
    }
    return $sassBrandingVars;
  }

  /**
   * Builds theme based on the raw theme vars.
   * If no valid vars are found, the theme will be deleted.
   *
   * @param array $rawThemeVars
   */
  public function buildTheme(array $rawThemeVars)
  {
    $themeVars = $this->determineThemeVars($rawThemeVars);

    // if any theme var is missing, we reset the theme
    if (count($themeVars) !== count($this->requiredBrandingVars)) {
      $this->resetTheme();
      return;
    }

    $this->compileSassFiles($themeVars);
  }

  /**
   * @param array $themeVars
   *
   * @throws \Cms\ExceptionStackException
   */
  protected function compileSassFiles(array $themeVars)
  {
    $sassBrandingVars = $this->convertVarsToScssCode($themeVars);

    $scssCompiler = $this->getScssComplier();
    $scssCompiler->setImportPaths($this->sourcePath);

    $this->createTargetDirectory();

    // create theme files
    CmsExceptionStack::reset();
    foreach ($this->themeFileNames as $file => $target) {
      try {
        $scssSource = file_get_contents(FS::joinPath($this->sourcePath, $file));
        $cssData = $scssCompiler->compile($sassBrandingVars . $scssSource, $file);
        file_put_contents(FS::joinPath($this->targetPath, $target), $cssData);
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }
  }

  /**
   * Empty all theme files
   */
  public function resetTheme()
  {
    $this->createTargetDirectory();

    // empty theme files
    CmsExceptionStack::reset();
    foreach ($this->themeFileNames as $file => $target) {
      try {
        FS::rmFile(FS::joinPath($this->targetPath, $target));
      } catch (\Exception $e) {
        CmsExceptionStack::addException($e);
      }
    }

    if (CmsExceptionStack::hasErrors()) {
      CmsExceptionStack::throwErrors();
    }
  }

  /**
   * Creates the target directory for the theming files
   */
  protected function createTargetDirectory()
  {
    FS::createDirIfNotExists($this->targetPath, true);
  }

  /**
   * @return ScssCompiler
   */
  protected function getScssComplier()
  {
    return new ScssCompiler();
  }
}
