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

use OCA\AgreeDisclaimer\Config\Config;
use OCA\AgreeDisclaimer\Utils;

/**
 * Main application class where all the services, controllers, hooks, and
 * application settings are registered
 */
class Application extends App {

    /** @var string    Applications name */
    private $appName;

    /** @var Config    Configuration settings */
    private $config;

    /** @var Utils    Helper functions */
    private $utils;

    /**
     * Creates an Application object and registers its related services,
     * user hooks, and settings
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
        $l10n = $server->getL10N($this->appName);
        $this->config = New Config(
                                $this, 
                                $server->getAppConfig(),
                                $l10n,
                                $server->getURLGenerator()
                        );
        $this->utils = New Utils($this, $l10n);
    }

    /**
     * Registers all services
     */
    public function registerAll() {
        $this->registerServices();
        //$this->registerHooks();
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
        /*
        $container->registerService(
            'SettingsController', function($c) {
                return new SettingsController (
                    $c->query('AppName'),
                    $c->query('Request')
                );
            }
        });
        */
    }

    /**
     * Registers all the application user hooks
     *
     * @param IAppContainer $container  Application container
     */
    public function registerHooks(IAppContainer $container) {
        $container = $this->getContainer();

        /**
         * Registers the preLogin hook to catch wether or not the user accepted
         * the disclaimer.
         */
        $container->registerService('UserHooks', function($c) {
            return new UserHooks(
                $c->query('ServerContainer')->getUserManager()
            );
        });
        $this->getContainer()->query('UserHooks')->register();
    }

    /**
     * Renders the template for the login page
     *
     * @return OCP\AppFramework\Http\TemplateResponse   The response for the
     *         login template
     */
    public function getDisclaimerForm() {
        $container = $this->getContainer();
        $session = $container->query('OCP\IUserSession');
        $templateResponse = null;
        if (!$session->isLoggedIn()) {
            $data = [
                'appName' => $this->appName,
            ];
            $templateResponse = new TemplateResponse($this->appName, 'login',
                                        $data, 'blank');
        }
        return $templateResponse;
    }

    /**
     * Gets the configuration of this application
     * @return Config   The configuration object
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Gets the utility helpers
     * @return Utils    The utility helpers
     */
    public function getUtils() {
        return $this->utils;
    }
}
