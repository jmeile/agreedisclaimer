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
$app = new Application();
$container = $app->getContainer();

// TODO: this should be moved into a separate class
$appId = $container->query('AppName');
$userId = $container->query('UserId');
$appManager = $container->query('OCP\App\IAppManager');
$session = $container->query('OCP\IUserSession');

if ($appManager->isEnabledForUser($appId, $userId) && !$session->isLoggedIn()) {
    $templateResponse = $app->getDisclaimerForm();
    return $templateResponse->render();
}
