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

use \OCP\AppFramework\Http\TemplateResponse;

/**
 * Renders the app settings on the admin page
 *
 * @return string   The template rendered as html
 */

\OCP\User::checkAdminUser();

$appId = \OCA\AgreeDisclaimer\AppInfo\Application::APP_ID;
$templateResponse = new TemplateResponse($appId, 'admin', [], 'blank');
return $templateResponse->render();
