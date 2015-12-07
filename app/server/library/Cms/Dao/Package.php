<?php


namespace Cms\Dao;

use Cms\Dao\Package\Source as PackageSource;
use Cms\Data\Package as DataPackage;

interface Package
{
  /**
   * resets the internal cache
   */
  public function resetCache();

  /**
   * returns all Packages of the given source
   *
   * @param PackageSource $packageSource
   *
   * @return  DataPackage[]
   */
  public function getAll(PackageSource $packageSource);
}
