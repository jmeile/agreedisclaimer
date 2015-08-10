<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\AppInfo;

/**
 * Creates the application and renders the login template for the anonymous user
 */

$app = new Application();
$app->registerAll();

$templateResponse = $app->getDisclaimerForm();
if ($templateResponse !== null){
    //The template won't be returned if the user is already logged in
    return $templateResponse->render();
}
