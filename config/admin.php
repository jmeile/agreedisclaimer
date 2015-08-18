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
use OCP\AppFramework\Http\TemplateResponse;

use OCA\AgreeDisclaimer\AppInfo\Application;

/**
 * Renders the app settings on the admin page
 *
 * @return string   The template rendered as html
 */

$app = new Application();
$appName = $app->getAppName();
$container = $app->getContainer();
$config = $app->getConfig();
$utils = $app->getUtils();

$defaultLang = $config->getDefaultLang();
$localeInfo = $utils->getAvailableLanguages($defaultLang);

//Fix it: I'm not sure if there is a better way of getting l10n from this
//script, ie: something like in the templates: $l->...
$l10n = $app->getContainer()->getServer()->getL10N($appName);
$userLang = $l10n->getLanguageCode();

$data = [
    'appName'             => $appName,
    'filePreffix'         => $container->query('filePreffix'),
    'datepickerAppFormat' => $config->getDatepickerDateFormat(),
    'cookieData'          => $config->getCookieData(true),
    'txtFileData'         => $config->getTxtFileData(false, true),
    'pdfFileData'         => $config->getPdfFileData(false, true),
    'textData'            => $config->getDisclaimerType(),
    'disclaimerTypes'     => $config->getDisclaimerTypes(),
    'userLang'            => $userLang,
    'currentLang'         => $localeInfo['activelanguage'],
    'commonLanguages'     => $localeInfo['commonlanguages'],
    'availableLanguages'  => $localeInfo['languages'],
];
$templateResponse = new TemplateResponse($appName, 'admin', $data,
                            'blank');
return $templateResponse->render();
