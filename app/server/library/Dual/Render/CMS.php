<?php
namespace Dual\Render;

/**
 * Diese Klasse dient fuer Konstanten, welche im gesamten System
 * (CMS + Rendere) beoetigt wird
 * (Klasse wir auch auf den Live-Server kopiert)
 *
 * @package    Dual
 */

class CMS
{
  // Render-Modi
  const RENDER_MODE_EDIT              = 'EDIT';
  const RENDER_MODE_PREVIEW           = 'PREVIEW';
  const RENDER_MODE_SHOW              = 'SHOW';
  
  // Modul-Typen
  const MODULE_TYPE_ALL               = '*';
  const MODULE_TYPE_DEFAULT           = 'default';
  const MODULE_TYPE_ROOT              = 'root';
  const MODULE_TYPE_EXTENSION         = 'extension';
  
  // Gibt alle Modultypen zurueck (ausser self::MODULE_TYPE_ALL)
  public static function getModuleTypes()
  {
    return array(
        self::MODULE_TYPE_DEFAULT,
        self::MODULE_TYPE_ROOT,
        self::MODULE_TYPE_EXTENSION
    );
  }
}
