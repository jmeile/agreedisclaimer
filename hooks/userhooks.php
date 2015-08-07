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

use OCP\IUserManager;
use OCP\IL10N;

// hint: private code usage is bad!
use OC\User\LoginException;

/**
 * Class defining the preLogin hook actions
 */
class UserHooks {

    private $userManager;
    private $l10n;
    private $appName;

    /**
     * Creates an UserHooks object
     *
     * @param \OC\User\Manager  UserManager used by the hook
     */
    public function __construct(IUserManager $userManager, IL10n $l10n, $AppName){
        $this->userManager = $userManager;
        $this->l10n = $l10n;
        $this->appName = $AppName;
    }

    /**
     * Registers the preLogin hook to catch wether or not the user accepted the
     * disclaimer
     */
    public function register() {
        $this->userManager->listen('\OC\User', 'preLogin', function($user, $password) {
            if(!isset($_POST[$this->appName . 'Checkbox'])) {
                $message = $this->l10n->t('Please read and ' .
                    'agree the disclaimer before proceeding');
                throw new LoginException($message);
            }
        });
    }
}
