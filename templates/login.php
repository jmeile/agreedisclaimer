<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Template that will be rendered on the login page
 */

$appId = $_['appId'];

//Gets the current user's language
$userLang = \OCA\AgreeDisclaimer\Utils::getUserLang();
if (!\OC_L10N::languageExists($appId, $userLang)) {
    #It can be that some language dialects hasn't being translated, so, a
    #suitable language will be searched. ie: if 'de_CH' isn't available, then
    #'de_DE' (formal german) will be used. In case that 'de_DE' isn't available,
    #then 'de' (informal german will be used). If no fallback language is found,
    #then the defined default language will be used. In case nothing is found,
    #then ownCloud will decide which language to use, which in most cases is
    #'en'.
    $langFallbacks = \OCA\AgreeDisclaimer\Utils::getFallbackLang($userLang);

    $defaultLangProp = $appId . 'DefaultLang';
    $defLang = \OCA\AgreeDisclaimer\Controller\SettingsController::getSetting(
               $defaultLangProp, 'en');

    if ($defLang !== $userLang) {
        $langFallbacks[] = $defLang; 
    }

    foreach ($langFallbacks as $langCode) {
        if (\OC_L10N::languageExists($appId, $langCode)) {
            \OC_L10N::forceLanguage($langCode);
            \OCP\Util::writeLog($appId, "The language: $userLang hasn't been " .
                "yet translated, falling back to: $langCode", \OCP\Util::WARN);
            break;
        }
    }
}

/**
 * Adds the javascript utilities to the login page
 */
script($appId, 'utils');

/**
 * Adds the javascript to the login page
 */
script($appId, 'login');

/**
 * Adds the style sheets file to the login page
 */
style($appId, 'login');
?>

<!-- Every html code after this gets ignored :-(
     That's why I have to code everything on javascript. I asked already about
     this on the developers maillist, but I didn't get any answer:
     * Returning Template for login page
       https://mailman.owncloud.org/pipermail/devel/2015-July/001446.html
-->
