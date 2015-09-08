<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\Hooks;

use OCA\AgreeDisclaimer\AppInfo\Application;

use OCP\IRequest;

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

    /** @var IRequest   Request from which the hook was called */
    private $request;

    /**
     * Creates an UserHooks object 
     *
     * @param IUserManager $userManager    UserManager used by the hook
     * @param IRequest     $request        Request from which the hook was
     *            called
     * @param IL10N        $l10n           Translation service
     */
    public function __construct(IUserManager $userManager, IRequest $request,
                        IL10N $l10n){
        $this->userManager = $userManager;
        $this->request = $request;
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
            $app = new Application();
            $appName = $app->getAppName(); 
            $config = $app->getConfig();

            $isDav = strpos($this->request->getScriptName(),
                '/remote.php') !== false;
            $disclaimerChecked = isset($_POST[$appName. 'Checkbox']);

            if (
                //For dav requests, don't throw the exception; otherwise sync
                //won't work
                (!$isDav)
                //If the "agree" checkbox wasn't checked, throw an exception
             && (!$disclaimerChecked)
            ){
                //If the "agree" checkbox wasn't checked, then an throw an
                //exception
                if ($config->getUseCookie()) {
                    //In case that the agree disclaimer cookies was set, expires
                    //it. This means that the user either unchecked the
                    //disclaimer or he hasn't checked it yet
                    $config->expireCheckedCookie();
                }
                $message = $this->l10n->t('Please read and ' .
                    'agree the disclaimer before proceeding');
                throw new LoginException($message);
            } else {
                if ($config->getUseCookie()) {
                    $config->setLastVisitCookie();
                    $config->setCheckedCookie();
                }
            }
        };
        $this->userManager->listen('\OC\User', 'preLogin', $callback);
    }
}
