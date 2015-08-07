<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\Hooks;

use OCA\AgreeDisclaimer\AppInfo\Application;
use OC\User\LoginException;

/**
 * Class defining the preLogin hook actions
 */
class UserHooks {

    private $userManager;

    /**
     * Creates an UserHooks object 
     *
     * @param \OC\User\Manager  UserManager used by the hook
     */
    public function __construct($userManager){
        $this->userManager = $userManager;
    }

    /**
     * Registers the preLogin hook to catch wether or not the user accepted the
     * disclaimer
     */
    public function register() {
        $callback = function($user, $password) {
            $appId = Application::APP_ID;
            if(!isset($_POST[$appId . 'Checkbox'])) {
                $message = \OCP\Util::getL10N($appId)->t('Please read and ' .
                    'agree the disclaimer before proceeding');
                throw new LoginException($message);
            }
        };
        $this->userManager->listen('\OC\User', 'preLogin', $callback);
    }
}
