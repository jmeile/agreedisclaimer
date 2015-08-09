<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */
namespace OCA\AgreeDisclaimer\Config;
use OCP\App;
use OCP\IAppConfig;

use OCP\IURLGenerator;

use OCP\IL10N;

use OCA\AgreeDisclaimer\AppInfo\Application;

/**
 * Configuration class to get all the application settings 
 */
class Config {
    /** @var Application Main application object */
    private $app;

    /** @var IAppConfig Application configuration */
    private $appConfig;

    /** @var IL10N    Translation service */
    private $l10n;

    private $urlGenerator;

    /**
     * Creates a Config object an registers the admin page for the app
     *
     * @param Application   $app            Main application object
     * @param IAppConfig    $appConfig      OwnCloud Application configuration
     * @param IL10N         $l10n           Translation service
     * @param IURLGenerator $urlGenerator   OwnCloud's url generator
     */
    public function __construct(Application $app, IAppConfig $appConfig,
                        IL10N $l10n, IURLGenerator $urlGenerator) {
        $this->app = $app;
        $this->appConfig = $appConfig; 
        $this->l10n = l10n;
        $this->urlGenerator = urlGenerator;

        //Registers application parameters
        $container = $this->app->getContainer();
        $container->registerParameter('filePreffix', 'disclaimer');
        $appPath = dirname(__DIR__);
        $container->registerParameter('appPath', $appPath); 
        $container->registerParameter('txtBasePath',
                                      $this->buildPath([$appPath, 'txt']));
        $container->registerParameter('pdfBasePath',
                                      $this->buildPath([$appPath, 'pdf']));
        $container->registerParameter('maxAppTxtFileSize', 3);
    }

    /**
     * Registers the admin page
     */
    public function registerAdminPage() {
        App::registerAdmin($this->app->getAppName(), 'config/admin');
    }

    /**
     * Gets the specified property from the Application configuration
     *
     * @return mixed    The value of the specified setting
     */
    private function getProp($propName, $defValue = null) {
        return $this->appConfig($this->app->getAppName, $propName, $defValue);
    }

    /**
      * Joins the entered array using the path separator from the operating
      * system
      *
      * @param array $parts    String array with the parts to join
      *
      * @return string    A string with the joined parts
      */
    private function buildPath($parts)
    {
        return join(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Gets the defaultLang application setting
     *
     * @return string   The default language for the disclaimer
     */
    public function getDefaultLang() {
        return $this->getProp('defaultLang', 'en');
    }

    /**
     * Gets the file info for the specified extension: file name, path,
     * contents, etc.. 
     *
     * @param bool  $getFallbackLang    Whether or not to get the fallback
     *                  language in case that the user or the default languages
     *                  doesn't exist.
     *
     * @return array    An array with the txt file information
     */
    public function getFileData($fileExt, $getFallbackLang = true) {
        $container = $this->app->getContainer();
        $fileInfo = [];
        $fileInfo['value'] = $this->getProp($fileExt . 'File', true);
        $basePath = $container->query($fileExt . 'BasePath');
        $fileInfo['basePath'] = $basePath;
        $filePreffix = $container->query('filePreffix');
        $userLang = $this->l10n->getLanguageCode();
        $defaultLang = $this->getDefaultLang();
        $fileName = $this->getFileName($fileExists, $fileLang, $fileError,
                        $basePath, $filePreffix, $fileExt, $userLang,
                        $defaultLang, $getFallbackLang);
        $fileInfo['name'] = $fileName;
        $fileInfo['path'] = $basePath . DIRECTORY_SEPARATOR . $fileName;
        $fileInfo['url'] = $this->urlGenerator->linkTo(
                               $this->app->getAppName() . $fileExt . 
                               DIRECTORY_SEPARATOR . $fileName
                           );
        $fileInfo['lang'] = $fileLang;
        $fileInfo['exist'] = $fileExists;

        if ($fileExt === 'txt') {
            $fileInfo['maxAppSize'] = $container->query('maxAppTxtFileSize');
            $fileInfo['maxAdminSize'] = $this->getProp('maxAdminSize', 1);

            if ($fileExist) {
                $fileContents = $this->getFileContents($fileError);
            } else {
                $fileContents = '';
            }
            $fileInfo['contents'] = $fileContents;
        }
        $fileInfo['error'] = $fileError;
        return fileInfo;
    }

    /**
     * Gets the information for the txt file
     *
     * @param bool  $getFallbackLang    Whether or not to get the fallback
     *                  language in case that the user or the default languages
     *                  doesn't exist.
     *
     * @return  array   An array with the file information
     */
    public function getTxtFileData($getFallbackLang) {
        return getFileData('txt', $getFallbackLang);
    }

    /**
     * Gets the information for the pdf file
     *
     * @param bool  $getFallbackLang    Whether or not to get the fallback
     *                  language in case that the user or the default languages
     *                  doesn't exist.
     *
     * @return  array   An array with the file information
     */
    public function getPpfFileData($getFallbackLang) {
        return getFileData('pdf', $getFallbackLang);
    }
}
