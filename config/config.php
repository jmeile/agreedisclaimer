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
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;

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
        return $this->appConfig->getValue($this->app->getAppName, $propName,
                                     $defValue);
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

    public function getFileName(&$fileExists, &$fileLang, &$fileError,
                        $basePath, $filePreffix, $fileExt, $userLang,
                        $defaultLang, $getFallbackLang) {
        $fileName = $filePreffix . '_' . $userLang . '.' . $fileExt;
        $filePath = $this->buildPath([$basePath, $fileName]);
        $fileExists = file_exists($filePath);
        $fileLang = $userLang;
        $utils = $this->app->getUtils();
        $langFallbacks = $utils->getFallbackLang($userLang);
        $fileError = '';
        if (!$fileExists) {
            $fileError = $this->l10n->t('%s doesn\'t exist.',
                             $fileLang . '<br/>') . ' ' .
                             $this->l10n->t('Please contact the webmaster');
            $languages = array();
            if ($getFallbackLang) {
                $languages = array_merge($languages, $langFallbacks);
            }
            if ($userLang !== $defaultLang) {
                $languages[] = $defaultLang;
            }
            foreach ($languages as $langCode) {
                $fileName = $filePreffix . '_' . $langCode . '.' . $fileExt;
                $filePath = $this->buildPath([$basePath, $fileName]);
                $fileExists = file_exists($filePath);
                if ($fileExists) {
                    $fileLang = $langCode;
                    break;
                }
            }
            if (!$fileExists && count($languages) > 1) {
                $fileError = $this->l10n->t('Neither the file:' .
                    ' %s nor: %s exist',
                    ['<br/>'. $userLangFile. '<br/><br/>',
                     '<br/>' . $filePath . '<br/><br/>']) . '. ' .
                    \OCP\Util::getL10N($this->app->getAppName())
                        ->t('Please contact the webmaster');
            }
        }
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
        $utils = $this->app->getUtils();
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
        $fileInfo['path'] = $this->buildPath([$basePath, $fileName]);
        $fileInfo['url'] = $this->urlGenerator->linkTo(
                               $this->buildPath([
                                   $this->app->getAppName() . $fileExt, 
                                   $fileName
                               ])
                           );
        $fileInfo['lang'] = $fileLang;
        $fileInfo['exist'] = $fileExists;

        if ($fileExt === 'txt') {
            $fileInfo['maxAppSize'] = $container->query('maxAppTxtFileSize');
            $fileInfo['maxAdminSize'] = $this->getProp('maxAdminSize', 1);

            if ($fileExist) {
                $fileContents = $this->getFileContents($fileError);
                if ($file_contents === false) {
                    //You have to use === otherwise the empty string will be
                    //evaluated to false
                    $fileError = $this->l10n->t('Could not read contents ' .
                                    'from file: %s', $filePath) . '\n\n' .
                               $this->l10n->t('Make sure that the file ' .
                                    'exists and that it is readable by the '.
                                    'apache user');
                    //This ensures that carriage returns appear in a textarea
                    $message = $utils->fixCarriageReturns($message);
                    $file_contents = '';
                    $fileError = $message; 
                }
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
        return $this->getFileData('txt', $getFallbackLang);
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
    public function getPdfFileData($getFallbackLang) {
        return $this->getFileData('pdf', $getFallbackLang);
    }
}
