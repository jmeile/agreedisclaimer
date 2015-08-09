<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */
namespace OCA\AgreeDisclaimer;
use OCP\IL10N;

use OCA\AgreeDisclaimer\AppInfo\Application;


/**
 * Class with some helper functions 
 */
class Utils {
    /** 
      * @var array    Language fallbacks. The leftmost language will have
      *               precedency. Please note that all languages will fall back
      *               to its root language by defualt, ie: 'es_CO' will fall
      *               back to 'es'. This array is intended to special cases
      *               were you want another thing, ie: for an installation in
      *               Colombia, you may want that all spanish variants fall back
      *               first to 'es_CO', so you will have to insert:
      *                   [ 'es' => ['es_CO', 'es'] ]
      *               Then supposed that 'es_BO' (bolivian spanish) isn't
      *               translated yet, the language will be shorted to 'es', then
      *               according to the fall back array, it will first look
      *               'es_CO', then if this language isn't defined, it will try
      *               'es'. In case that the last language on the array isn't
      *               defined, then OwnCloud will decide to use 'en' (English).
      */
    private $langFallbacks = [
        //This precedency was defined in order to guarantee that if a dialect
        //isn't translated, ie: 'de_CH', it fall backs first to 'de_DE' (formal
        //german), then to 'de' (informal german)
        'de' => [ 'de_DE', 'de' ]
    ];

    /** @var Application Main application object */
    private $app;

    /** @var IL10N    Translation service */
    private $l10n;

    /**
     * Creates an Utility instance
     *
     * @param Application $app     Main application object
     * @param IL10N       $l10n    Translation service
     */
    public function __construct(Application $app, IL10N $l10n) {
        $this->app = $app;
        $this->l10n = $l10n;
    }

    /**
     * Gets the fall back languages for the entered code
     *
     * @param string    $userLang   language for which the fall backs are going
     *                              to be recovered
     *
     * @return array    An array with the fall back languages for the entered
     *                  language
     *
     * @see $langFallbacks
     */
    public function getFallbackLang($userLang) {
        $rootLang = $userLang;
        if (strlen($userLang) == 5) {
            $langParts = explode('_', $userLang);
            $rootLang = $langParts[0];
        }

        $langFallbacks = [$rootLang];
        if (isset($this->langFallbacks[$rootLang])) {
            $langFallbacks = $this->langFallbacks[$rootLang];
        }
        return $langFallbacks;
    }

    /**
     * Fixes the carriage returns in order to make them rendering properly on a
     * html textarea
     *
     * @return string   The sring with the fixed carriage returns
¨    */
    public function fixCarriageReturns($str) {
        $result = preg_replace('/(\\\r)?\\\n/', "\n", $str);
        return $result;
    }

    /**
     * Gets all the available languages
     *
     * @param   string  $defaultLang    Current used language
     *
     * @return array    An array of the form:
     *     ['languages'       => <languages>,
     *      'commonlanguages' => <common_languages>,
     *      'activelanguage'  => <active_language>]
     *     where:
     *     - languages is an array with all the ownCloud languages (except the
     *       common ones) of the form:
     *          [['code' => <lang_code>, 'name' => <translated_lang_name], ...]
     *     - commonlanguages is an array with the common ownCloud languages
     *     - active_language is the current used language
     *
     * @remarks: This code was taken from:
     *       * <ownCloudRoot>/settings/personal.php
     *       Unfortunatelly there isn't an utility for this at the
     *       moment of writting
     */
    public function getAvailableLanguages($defaultLang)
    {
        $userLang = $defaultLang;

        //It would be nice this method to be a public method (not static) of
        //L10N
        $languageCodes = \OC_L10N::findAvailableLanguages();

        // array of common languages
        $commonlangcodes = array(
            'en', 'es', 'fr', 'de', 'de_DE', 'ja', 'ar', 'ru', 'nl', 'it',
            'pt_BR', 'pt_PT', 'da', 'fi_FI', 'nb_NO', 'sv', 'tr', 'zh_CN', 'ko'
        );

        //This is also an ugly hack, but it was taken from the OwnCloud core lib
        $languageNames = include \OC::$SERVERROOT .
            '/settings/languageCodes.php';

        $languages = array();
        $commonlanguages = array();
        $server = $this->app->getContainer()->getServer();
        foreach($languageCodes as $lang) {
            $l = $server->getL10N('settings', $lang);
            if ( substr($l->t('__language_name__'), 0, 1) !== '_') {
                //first check if the language name is in the translation file
                $ln = array(
                    'code' => $lang,
                    'name' => (string)$l->t('__language_name__')
                );
            } elseif(isset($languageNames[$lang])) {
                $ln=array('code' => $lang, 'name' => $languageNames[$lang]);
                } else { //fallback to language code
                $ln=array('code'=>$lang, 'name'=>$lang);
            }

            // put apropriate languages into apropriate arrays, to print them
            // sorted used language -> common languages -> divider -> other
            //languages
            if ($lang === $userLang) {
                $userLang = $ln;
            } elseif (in_array($lang, $commonlangcodes)) {
                $commonlanguages[array_search($lang, $commonlangcodes)]=$ln;
            } else {
                $languages[]=$ln;
            }
        }

        ksort($commonlanguages);

        // sort now by displayed language not the iso-code
        usort($languages, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return array(
            'languages' => $languages,
            'commonlanguages' => $commonlanguages,
            'activelanguage' => $userLang,
        );
    }
}
