<?php
namespace Dual\Render;

/**
 * Warning: Not thread safe!
 */
class ModuleTranslationObject
{

    const TRANSLATION_MAP_FILE = 'moduleTranslation.php';

    private $zendTranslate = null;

    /**
     * Loads the translation file out of the given module path
     *
     * @param string $modulePath
     *            -- Path to the module directory
     */
    public function __construct($modulePath)
    {
        $translationFilepath = $modulePath . DIRECTORY_SEPARATOR . self::TRANSLATION_MAP_FILE;
        $translation = (include $translationFilepath);
        $this->initTranslation($translation);
    }

    /**
     * Returns the current language of the client interface.
     * Or in other words: The language of the current rukzuk user.
     */
    public function getCurrentClientLanguage()
    {
      return RenderContext::getLocale();
    }

    /**
     * Returns the translation for $key
     *
     * @param string $key
     *            -- Lookup key for the translation
     * @param string $currentLanguage
     *            -- The destination language (defaults to the current user's
     *            language)
     * @return string
     */
    public function translate($key, $currentLanguage = null)
    {
      if (is_null($this->zendTranslate)) {
          return $key;
      }
      if (is_null($currentLanguage)) {
          $currentLanguage = $this->getCurrentClientLanguage();
      }
        return $this->zendTranslate->translate($key, $currentLanguage);
    }

    /**
     * Returns an array of all translations for the given key
     *
     * @param string $key -- the translation key
     * @param array $locales -- the lookup languages
     * @return array -- an array with all transltions (never null)
     */
    public function translateToArray($key, $locales = null)
    {
        // Clean up the locales parameter
      if (is_null($locales)) {
          $locales = $this->zendTranslate->getList();
        ;
      }
        // Collecting all translations
        $resultArray = array();
      foreach ($locales as $local) {
          $resultArray[$local] = $this->translate($key, $local);
      }
        return $resultArray;
    }

    /**
     * Returns a json string that encodes an array of all translations
     * for the given key.
     *
     * @param string $key -- the translation key
     * @param array $locales -- the lookup languages
     * @return string -- json array string
     */
    public function translateToJson($key, $locales = null)
    {
      return json_encode($this->translateToArray($key));
    }

    /**
     * Initializes the internal translation map
     *
     * @param array $translationArray
     *            -- array ('locale' => array('key' => 'translation'))
     */
    protected function initTranslation($translationArray)
    {
      if (! is_array($translationArray) || empty($translationArray)) {
          // We have a broken translation file
          return;
      }
      foreach ($translationArray as $translationLocale => $translationContent) {
        if (is_null($this->zendTranslate)) {
            $this->initZendTranslate(
                $translationLocale,
                $translationContent
            );
        } else {
            $this->addTranslation($translationLocale, $translationContent);
        }
      }
    }

    /**
     * Creates a new Zend_Translate object.
     * Used as the internal tranlation map.
     *
     * @param string $translationLocale
     * @param array $translationContent
     */
    protected function initZendTranslate(
        $translationLocale,
        $translationContent
    ) {
        $this->zendTranslate = new \Zend_Translate(
            array(
                        'adapter' => 'Zend_Translate_Adapter_Array',
                        'content' => $translationContent,
                        'locale' => $translationLocale,
                        'route' => $this->getLocaleRoute()
                )
        );
    }

    /**
     * Returns the default locale route array.
     *
     * @return multitype:string
     */
    protected function getLocaleRoute()
    {
        // TODO: Load this value from the rukzuk config ini file.
        return array(
                'de' => 'en'
        );
    }

    /**
     * Adds an other locale to the Zend_Translate object
     *
     * @param string $translationLocale
     * @param array $translationContent
     */
    protected function addTranslation($translationLocale, $translationContent)
    {
        $this->zendTranslate->addTranslation(
            array(
                        'locale' => $translationLocale,
                        'content' => $translationContent
                )
        );
    }
}
