<?php
namespace Seitenbau;

/**
 * @package    Seitenbau
 * @subpackage Mimetype
 */

/**
 * Stellt die Funktionalitaeten zur Ermittlung des Mime-types bereit
 *
 * @package    Seitenbau
 * @subpackage Mimetype
 */
class Mimetype
{
  /**
   * Mimetype-Liste aller bekannten Extensions und deren Mimetypes
   * @access protected
   */
  private static $mimeTypeList;


  /**
   * Liefert den zur uebergebenen Datei gehoerenden Mime-Type.
   *
   * @param string $file zu ueberpruefende Datei
   * @return string
   * @access public
   */
  protected static function loadMimeTypeList()
  {
    // Liste setzen
    self::$mimeTypeList = array
        (
          'ez' => 'application/andrew-inset',             'hqx' => 'application/mac-binhex40',
          'cpt' => 'application/mac-compactpro',          'doc' => 'application/msword',
          'bin' => 'application/octet-stream',            'dms' => 'application/octet-stream',
          'lha' => 'application/octet-stream',            'lzh' => 'application/octet-stream',
          'exe' => 'application/octet-stream',            'class' => 'application/octet-stream',
          'so' => 'application/octet-stream',             'dll' => 'application/octet-stream',
          'oda' => 'application/oda',                     'pdf' => 'application/pdf',
          'ai' => 'pplication/postscript',                'eps' => 'application/postscript',
          'ps' => 'application/postscript',               'smi' => 'application/smil',
          'smil' => 'application/smil',                   'mif' => 'application/vnd.mif',
          'xls' => 'application/vnd.ms-excel',            'ppt' => 'application/vnd.ms-powerpoint',
          'wbxml' => 'application/vnd.wap.wbxml',         'wmlc' => 'application/vnd.wap.wmlc',
          'wmlsc' => 'application/vnd.wap.wmlscriptc',    'bcpio' => 'application/x-bcpio',
          'vcd' => 'application/x-cdlink',                'pgn' => 'application/x-chess-pgn',
          'cpio' => 'application/x-cpio',                 'csh' => 'application/x-csh',
          'dcr' => 'application/x-director',              'dir' => 'application/x-director',
          'dxr' => 'application/x-director',              'dvi' => 'application/x-dvi',
          'spl' => 'application/x-futuresplash',          'gtar' => 'application/x-gtar',
          'tgz' => 'application/x-gtar',                  'gz' => 'application/x-gzip',
          'hdf' => 'application/x-hdf',                   'js' => 'application/x-javascript',
          'skp' => 'application/x-koan',                  'skd' => 'application/x-koan',
          'skt' => 'application/x-koan',                  'skm' => 'application/x-koan',
          'latex' => 'application/x-latex',               'nc' => 'application/x-netcdf',
          'cdf' => 'application/x-netcdf',                'sh' => 'application/x-sh',
          'shar' => 'application/x-shar',                 'swf' => 'application/x-shockwave-flash',
          'sit' => 'application/x-stuffit',               'sv4cpio' => 'application/x-sv4cpio',
          'sv4crc' => 'application/x-sv4crc',             'tar' => 'application/x-tar',
          'tcl' => 'application/x-tcl',                   'tex' => 'application/x-tex',
          'texinfo' => 'application/x-texinfo',           'texi' => 'application/x-texinfo',
          't' => 'application/x-troff',                   'tr' => 'application/x-troff',
          'roff' => 'application/x-troff',                'man' => 'application/x-troff-man',
          'me' => 'application/x-troff-me',               'ms' => 'application/x-troff-ms',
          'ustar' => 'application/x-ustar',               'src' => 'application/x-wais-source',
          'xhtml' => 'application/xhtml+xml',             'xht' => 'application/xhtml+xml',
          'xml' => 'application/xml',                     'dtd' => 'application/xml-dtd',
          'zip' => 'application/zip',                     'au' => 'audio/basic',
          'snd' => 'audio/basic',                         'mid' => 'audio/midi',
          'midi' => 'audio/midi',                         'kar' => 'audio/midi',
          'mpga' => 'audio/mpeg',                         'mp2' => 'audio/mpeg',
          'mp3' => 'audio/mpeg',                          'aif' => 'audio/x-aiff',
          'aiff' => 'audio/x-aiff',                       'aifc' => 'audio/x-aiff',
          'm3u' => 'audio/x-mpegurl',                     'ram' => 'audio/x-pn-realaudio',
          'rm' => 'audio/x-pn-realaudio',                 'rpm' => 'audio/x-pn-realaudio-plugin',
          'ra' => 'audio/x-realaudio',                    'wav' => 'audio/x-wav',
          'pdb' => 'chemical/x-pdb',                      'xyz' => 'chemical/x-xyz',
          'bmp' => 'image/bmp',                           'gif' => 'image/gif',
          'ief' => 'image/ief',                           'jpeg' => 'image/jpeg',
          'jpg' => 'image/jpeg',                          'jpe' => 'image/jpeg',
          'png' => 'image/png',                           'tiff' => 'image/tiff',
          'tif' => 'image/tiff',                          'djvu' => 'image/vnd.djvu',
          'djv' => 'image/vnd.djvu',                      'wbmp' => 'image/vnd.wap.wbmp',
          'ras' => 'image/x-cmu-raster',                  'pnm' => 'image/x-portable-anymap',
          'pbm' => 'image/x-portable-bitmap',             'pgm' => 'image/x-portable-graymap',
          'ppm' => 'image/x-portable-pixmap',             'rgb' => 'image/x-rgb',
          'xbm' => 'image/x-xbitmap',                     'xpm' => 'image/x-xpixmap',
          'xwd' => 'image/x-xwindowdump',                 'igs' => 'model/iges',
          'iges' => 'model/iges',                         'msh' => 'model/mesh',
          'mesh' => 'model/mesh',                         'silo' => 'model/mesh',
          'wrl' => 'model/vrml',                          'vrml' => 'model/vrml',
          'css' => 'text/css',                            'html' => 'text/html',
          'htm' => 'text/html',                           'asc' => 'text/plain',
          'txt' => 'text/plain',                          'rtx' => 'text/richtext',
          'rtf' => 'text/rtf',                            'sgml' => 'text/sgml',
          'sgm' => 'text/sgml',                           'tsv' => 'text/tab-separated-values',
          'wml' => 'text/vnd.wap.wml',                    'wmls' => 'text/vnd.wap.wmlscript',
          'etx' => 'text/x-setext',                       'xml' => 'text/xml',
          'xsl' => 'text/xml',                            'mpeg' => 'video/mpeg',
          'mpg' => 'video/mpeg',                          'mpe' => 'video/mpeg',
          'qt' => 'video/quicktime',                      'mov' => 'video/quicktime',
          'mxu' => 'video/vnd.mpegurl',                   'avi' => 'video/x-msvideo',
          'movie' => 'video/x-sgi-movie',                 'ice' => 'x-conference/x-cooltalk',
          'wmv' => 'video/x-ms-wmv',                      'mp4' => 'video/mp4',
          '3g2' => 'video/x-3gpp2',                       '3gp' => 'video/3gpp',
          'svg' => 'image/svg+xml',                       'svgz' => 'image/svg+xml',
          'flv' => 'video/x-flv',                         'woff' => 'application/font-woff',

          // Neue Office 2007 Dateiformate
          'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
          'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
          'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
          'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
          'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
          'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
          'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
          'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
          'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
          'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
          'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
          'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
          'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
          'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
          'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
          'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
          'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
          'one'  => 'application/onenote',
          'onetoc2' => 'application/onenote',
          'onetmp' => 'application/onenote',
          'onepkg' => 'application/onenote',
          'thmx' => 'application/vnd.ms-officetheme'
        );
  }

  /**
   * Liefert die komplette Mime-Type Liste
   *
   * @return array
   * @access public
   */
  public static function getMimetypeList()
  {
    // Array mit Dateierweiterungen evtl. laden
    if (!is_array(self::$mimeTypeList)) {
    // Liste laden
      self::loadMimeTypeList();
    }

    // Liste aller Mime-Typs zurueckliefer
    return self::$mimeTypeList;
  }

  /**
   * Liefert den zur uebergebenen Datei gehoerenden Mime-Type.
   *
   * @param string $file zu ueberpruefende Datei
   * @return string
   * @access public
   */
  public static function getMimetype($file)
  {
    // Dateierweiterung ermitteln und Mime-Type zurueckgeben
    return self::getMimetypeByExtension(substr(strrchr($file, '.'), 1));
  }

  /**
   * Liefert den zur uebergebenen Dateierweiterung ( .doc ) gehoerenden Mime-Type.
   *
   * @param string $file zu ueberpruefende Dateierweiterung
   * @return string
   * @access public
   */
  public static function getMimetypeByExtension($extension)
  {
    // Dateierweiterung ueberpruefen
    if (!empty($extension)) {
    // Array mit Dateierweiterungen evtl. laden
      if (!is_array(self::$mimeTypeList)) {
      // Liste laden
        self::loadMimeTypeList();
      }

      // Dateierweiterungs-Liste vorhanen
      if (is_array(self::$mimeTypeList)) {
        $extension = strtolower($extension);
        if (!empty(self::$mimeTypeList[$extension])) {
        // Mime-Type zurueckgeben
          return self::$mimeTypeList[$extension];
        }
      }
    }

    // Mime-Type nicht gefunden
    return 'application/octet-stream';
  }
}
