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

use OCP\IUserManager;

use OCP\IL10N;

/**
 * Class defining the preLogin hook actions
 */
class UserHooks {

    /** @var IUserManager   UserManager used by the hook */
    private $userManager;

    /** @var IL10N    Translation service */
    private $l10n;

    /**
     * Creates an UserHooks object 
     *
     * @param IUserManager $userManager    UserManager used by the hook
     * @param IL10N        $l10n           Translation service
     */
    public function __construct(IUserManager $userManager, IL10N $l10n){
        $this->userManager = $userManager;
        $this->l10n = $l10n;
    }

    /**
     * Registers the preLogin hook to catch wether or not the user accepted the
     * disclaimer
     *
     * @param string $appName   The app's name
     */
    public function register($appName) {
        $callback = function($user, $password) {
            //Fix it: How can we get the appName from here?
            $appName = 'agreedisclaimer';

            if(!isset($_POST[$appName. 'Checkbox'])) {
                //If the "agree" checkbox wasn't checked, then an throw an
                //exception
                $message = $this->l10n->t('Please read and ' .
                    'agree the disclaimer before proceeding');
                throw new LoginException($message);
            }
        };
        $this->userManager->listen('\OC\User', 'preLogin', $callback);
    }
}
