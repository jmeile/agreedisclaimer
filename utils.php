<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer;

class Utils {
    /**
     * Gets the language of the user, including anonymous 
     *
     * @return The user language
     */
    public static function getUserLang() {
        $config = \OC::$server->getConfig();
        $lang = \OC_L10N::findLanguage();
        $userLang = $config->getUserValue(\OC_User::getUser(), 'core', 'lang',
            $lang);
        return $userLang;
    }

    /** Define your language fallbacks here. The leftmost language will have
      * precedency
      */
    public static $LANG_FALLBACKS = [
        'de' => [ 'de_DE', 'de' ]
    ];

    /**
     * Gets the fall back languages for the entered one
     *
     * @param string    $userLang   language for which the fall backs are going
     *                              to be recovered
     *
     * @return array    An array with the fall back languages for the entered
     *                  language
     *
     * @remarks: in some situations when you have a country specific locale, ie:
     *  'de_CH', you will want it to fallback to 'de_DE' (formal german), then
     *  to 'de' (unformal german). This function will look for the language
     *  fallbacks and will returned them. In case that there is no language
     *  fallback, then the root language will be returned, ie: for 'es_CO', the
     *  root language is 'es'.
     *
     *  Another case would be if you are on a country like Brazil and you want
     *  that 'pt_PT' and 'pt' fallback to 'pt_BR' in that case, you will have to
     *  modify the LANG_FALLBACKS constant and add: 'pt' => [ 'pt_BR', 'pt' ]
     */
    public static function getFallbackLang($userLang) {
        $rootLang = $userLang;
        if (strlen($userLang) == 5) {
            $langParts = explode('_', $userLang);
            $rootLang = $langParts[0];
        }

        $langFallbacks = [$rootLang];
        if (isset(self::$LANG_FALLBACKS[$rootLang])) {
            $langFallbacks = self::$LANG_FALLBACKS[$rootLang];
        }
        return $langFallbacks; 
    }

    /**
     * Fixes the carriage returns in order to make them rendering properly on a
     * html textarea
     *
     * @return string   The sring with the fixed carriage returns
¨    */
    public static function fixCarriageReturns($str) {
        $result = preg_replace('/(\\\r)?\\\n/', "\n", $str);
        return $result;
    }
}
