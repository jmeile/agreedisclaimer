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
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

use OCP\AppFramework\Http\TemplateResponse;

use OCA\AgreeDisclaimer\Hooks\UserHooks;
use OCA\AgreeDisclaimer\Controller\SettingsController;
use OCA\AgreeDisclaimer\Config\Config;
use OCA\AgreeDisclaimer\Utils;

/**
 * Main application class where all the services, controllers, hooks, and
 * application settings are registered
 */
class Application extends App {

    /** @var string    Applications name */
    private $appName;

    /** @var IL10N    Translation service */
    private $l10n;

    /** @var Config    Configuration settings */
    private $config;

    /** @var Utils    Helper functions */
    private $utils;

    /**
     * Creates an Application object
     */
    public function __construct(array $urlParams=array()) {
        //This won't work on symlinks; it will return the name of the linked
        //folder, instead of the link's name, so, I had to hard code the app's
        //name
        //$this->appName = basename(dirname(__DIR__));
        $this->appName = 'agreedisclaimer';

        parent::__construct($this->appName, $urlParams);
        $container = $this->getContainer();
        $server = $container->getServer();
        $this->l10n = $server->getL10N($this->appName);

        $this->config = New Config(
                                $this,
                                $server->getAppConfig(),
                                $this->l10n,
                                $server->getURLGenerator(),
                                $server->getLogger()
                        );
        $this->utils = New Utils($this, $this->l10n);
    }

    /**
     * Registers all services
     */
    public function registerAll() {
        $this->registerServices();
        $this->registerHooks();
        $this->config->registerAdminPage();
    }

    /**
     * Gets the application's name 
     *
     * @return string The application's name
     */
    public function getAppName() {
        return $this->appName;
    }

    /**
     * Registers all the application services
     */
    public function registerServices() {
        $container = $this->getContainer();

        /**
         * Registers the translation service
         */
        $container->registerService(
            'L10N', function(IAppContainer $c) {
                return $c->getServer()->getL10N($c->query('AppName'));
            }
        );

        /**
         * Registers the logging service
         */
        $container->registerService(
            'Logger', function($c) {
                return $c->getServer()->getLogger();
            }
        );

        /**
         * Registers the controllers
         */
        $container->registerService(
            'SettingsController', function($c) {
                return new SettingsController (
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->getServer()->getL10N($c->query('AppName')),
                    $this->config
                );
            }
        );
    }

    /**
     * Registers all the application user hooks
     */
    public function registerHooks() {
        $container = $this->getContainer();

        /**
         * Registers the preLogin hook to catch wether or not the user accepted
         * the disclaimer.
         */
        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserManager(),
                $c->getServer()->getL10N($c->query('AppName'))
            );
        });
        $this->getContainer()->query('UserHooks')->register($this->appName);
    }

    /**
     * Renders the template for the login page
     *
     * @return TemplateResponse   The response for the login template
     */
    public function getDisclaimerForm() {
        $container = $this->getContainer();
        $session = $container->query('OCP\IUserSession');
        $templateResponse = null;
        if (!$session->isLoggedIn()) {
            $userLang = $this->l10n->getLanguageCode();

            //Fix it: No way of getting rid of this static call. There is no
            //class method on OwnCloud that does this
            if (!\OC_L10N::languageExists($this->appName, $userLang)) {
                //It can be that some language dialects hasn't being translated,
                //so, a suitable language will be searched. ie: if 'de_CH' isn't
                //available, then 'de_DE' (formal german) will be used. In case
                //that 'de_DE' isn't available, then 'de' (informal german will
                //be used). If no fallback language is found, then the defined
                //default language will be used. In case nothing is found, then
                //ownCloud will decide which language to use, which in most
                //cases is 'en'.
                $langFallbacks = $this->utils->getFallbackLang($userLang);
                $defaultLang = $this->config->getDefaultLang();
                if ($defaultLang !== $userLang) {
                    $langFallbacks[] = $defaultLang;
                }

                foreach ($langFallbacks as $langCode) {
                    //Fix it: again, no way of getting rid of this static calls;
                    //the ownCloud library doesn't have any other way
                    if (\OC_L10N::languageExists($this->appName, $langCode)) {
                        $userLang = $langCode;
                        \OC_L10N::forceLanguage($userLang);
                        break;
                    }
                }
            }

            $data = [
                'appName'   => $this->appName,
            ];
            $templateResponse = new TemplateResponse($this->appName, 'login',
                                        $data, 'blank');
        }
        return $templateResponse;
    }

    /**
     * Gets the configuration of this application
     *
     * @return Config   The configuration object
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Gets the utility helpers
     *
     * @return Utils    The utility helpers
     */
    public function getUtils() {
        return $this->utils;
    }
}
