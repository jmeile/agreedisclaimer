<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\AppInfo;

/**
 * Creates the application only if it is enabled, then adds the javascript and
 * style sheets file to the login page.
 */
$appId = Application::APP_ID;
if ( \OCP\App::isEnabled($appId) ) {
    $app = new Application();

    /**
     * Renders the disclaimer form only if the user isn't logged in
     */
    if ( !\OCP\User::isLoggedIn() ) {
        $templateResponse = $app->getDisclaimerForm();
        return $templateResponse->render();
    }
}
