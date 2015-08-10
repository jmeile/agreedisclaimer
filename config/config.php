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

use \OCP\ILogger;

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

    /** 
     * @var IURLGenerator    Class used to generate urls to internal resources 
     */
    private $urlGenerator;

    /** Logger service */
    private $logger;

    /**
     * Creates a Config object an registers the admin page for the app
     *
     * @param Application   $app            Main application object
     * @param IAppConfig    $appConfig      OwnCloud Application configuration
     * @param IL10N         $l10n           Translation service
     * @param IURLGenerator $urlGenerator   OwnCloud's url generator
     */
    public function __construct(Application $app, IAppConfig $appConfig,
                        IL10N $l10n, IURLGenerator $urlGenerator,
                        ILogger $logger) {
        $this->app = $app;
        $this->appConfig = $appConfig; 
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;

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
     * @param string $propName    Name of the property to get
     * @param mixed  $defValue    Default value in case that the setting hasn't
     *                  been assigned
     *
     * @return mixed    The value of the specified setting
     */
    private function getProp($propName, $defValue = null) {
        return $this->appConfig->getValue($this->app->getAppName(), $propName,
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
     * Logs a message to the ownCloud's log file
     *
     * @param string $message    Message to log
     */
    public function log($message) {
        $this->logger->error($message,
            array('app' => $this->app->getAppName()));
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
     * Gets the file name for the specified user language.
     *
     * @param bool   $fileExists          Returns either if the file exists or
     *                   not
     * @param string $fileLang            Returned file language
     * @param string $fileError           Return error message
     * @param string $basePath            Base path of the file to get
     * @param string $filePreffix         Name preffix of the file
     * @param string $fileExt             Extension of the file to get
     * @param string $userLang            Current user language
     * @param string $defaultLang         Default language for the disclaimer
     * @param bool   $getFallbackLang     Whether or not to get the fallback
     *                   language in case that the user or the default
     *                   languages doesn't exist.
     * @param bool   $isAdminPage         Wheter or not is the admin page
     */
    public function getFileName(&$fileExists, &$fileLang, &$fileError,
                        $basePath, $filePreffix, $fileExt, $userLang,
                        $defaultLang, $getFallbackLang = true,
                        $isAdminPage = false) {
        $fileName = $filePreffix . '_' . $userLang . '.' . $fileExt;
        $filePath = $this->buildPath([$basePath, $fileName]);
        $fileExists = file_exists($filePath);
        $fileLang = $userLang;
        $utils = $this->app->getUtils();
        $langFallbacks = $utils->getFallbackLang($userLang);
        $fileError = '';
        $newLine = '<br/>';
        if ($isAdminPage) {
            $newLine = '\r\n';
        }
        if (!$fileExists) {
            $userLangFile = $filePath;
            $message = '%s doesn\'t exist';
            $fileError = $this->l10n->t($message,
                             $newLine . $filePath . $newLine) . '. ' .
                             $this->l10n->t('Please contact the webmaster');
            $fileError = $utils->fixCarriageReturns($fileError);

            //Logs only the english message
            $message = vsprintf($message, $filePath); 

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
                    $fileError = '';
                    $message = '';
                    break;
                }
            }
            if (!$fileExists && count($languages) > 1) {
                $message = 'Neither the file: %s nor: %s exist';
                $fileError = $this->l10n->t($message,
                    ['<br/>'. $userLangFile. '<br/><br/>',
                     '<br/>' . $filePath . '<br/><br/>']) . '. ' .
                    \OCP\Util::getL10N($this->app->getAppName())
                        ->t('Please contact the webmaster');
                $message = vsprintf($message, [$userLangFile, $filePath]);
            }

            if (($message !== '') && !$isAdminPage) {
                $this->log($message);
            }
        }
        return $fileName;
    }

    /**
     * Gets the file info for the specified extension: file name, path,
     * contents, etc.. 
     *
     * @param string $fileExt            Extension of the file to get
     * @param bool   $getFallbackLang    Whether or not to get the fallback
     *                   language in case that the user or the default languages
     *                   doesn't exist.
     * @param bool   $isAdminPage        Whether or not is the admin page
     * @param string $defaultLang        Default language. If null, it will
     *                   be adquired get from the app's configuration. Note that
     *                   it is only necessary pass this parameter on the admin
     *                   page while changing the default language. The reason of
     *                   this is that somethimes the ajax request that sets the
     *                   default language occurs after getting the info of the
     *                   file
     *
     * @return array    An array with the txt file information. It has the
     *                  following format:
     *                  [
     *                      'value'    => <is_setting_enabled>,
     *                      'name'     => <file_name>,
     *                      'basePath' => <root_folder_of_file>,
     *                      'path'     => <file_path>,
     *                      'url'      => <file_url>,
     *                      'lang'     => <file_language>,
     *                      'exists'   => <does_file_exist>,
     *                      'error'    => <error_message>,
     *                  ]
     *
     *                  Additionally this keys will be also set for txt files:
     *                  * maxAppSize:   Maximun hard coded size for txt files
     *                  * maxAdminSize: Maximun file size setup by the admin
     *                  * contents:     txt file contents
     *
     *                  For pdf this 'icon' key will contain an url to the pdf
     *                  icon
     */
    public function getFileData($fileExt, $getFallbackLang = true,
                        $isAdminPage = false, $defaultLang = null) {
        $utils = $this->app->getUtils();
        $container = $this->app->getContainer();
        $fileInfo = [];
        $fileInfo['value'] = $utils->strToBool(
                                 $this->getProp($fileExt . 'File', true)
                             );
        $basePath = $container->query($fileExt . 'BasePath');
        $fileInfo['basePath'] = $basePath;
        $filePreffix = $container->query('filePreffix');
        $userLang = $this->l10n->getLanguageCode();

        if ($defaultLang === null){
            $defaultLang = $this->getDefaultLang();
        }

        if ($isAdminPage) {
            //For the admin page only the default language will be retreived
            $userLang = $defaultLang;
        }
        $fileName = $this->getFileName($fileExists, $fileLang, $fileError,
                        $basePath, $filePreffix, $fileExt, $userLang,
                        $defaultLang, $getFallbackLang, $isAdminPage);
        $fileInfo['name'] = $fileName;
        $fileInfo['path'] = $this->buildPath([$basePath, $fileName]);
        $fileInfo['url'] = $this->urlGenerator->linkTo(
                               $this->app->getAppName(),
                               $this->buildPath([$fileExt, $fileName])
                           );
        $fileInfo['lang'] = $fileLang;
        $fileInfo['exists'] = $fileExists;

        if ($fileExt === 'txt') {
            $fileInfo['maxAppSize'] = $container->query('maxAppTxtFileSize');
            $fileInfo['maxAdminSize'] = $this->getProp('maxAdminTxtSize', 1);

            if ($fileExists) {
                $fileContents = $this->getFileContents($fileInfo['path'],
                                    $fileInfo['maxAdminSize'], $fileError,
                                    $isAdminPage);
            } else {
                $fileContents = '';
            }
            $fileInfo['contents'] = $fileContents;
        } else {
            $fileInfo['icon'] = $this->urlGenerator->linkTo(
                                    $this->app->getAppName(),
                                    $this->buildPath(['pdf', 'icon.png'])
                                );
        }
        $fileInfo['error'] = $fileError;
        return $fileInfo;
    }

    /**
     * Gets the contents of a text file
     *
     * @param string $filePath       Path of the file to read
     * @param int    $maxFileSize    Maximun megabytes to read
     * @param string $fileError      Returned error message
     * @param bool   $isAdminPage    Whether or not is the admin page
     *
     * @return string    The file contents
     */
    function getFileContents($filePath, $maxFileSize, &$fileError,
                 $isAdminPage = false) {
        $newLine = '<br/>';
        if ($isAdminPage) {
            $newLine = '\r\n';
        }
        $maxBytes = $maxFileSize * 1048576; 
        $fileContents = file_get_contents($filePath, false, null, 0, $maxBytes);
        if ($fileContents === false) {
            //You have to use === otherwise the empty string will be
            //evaluated to false
            $message = 'Could not read contents from file: %s';
            $fileError = $this->l10n->t($message,$filePath) .
                             $newLine . $newLine .
                         $this->l10n->t('Make sure that the file ' .
                            'exists and that it is readable by the '.
                            'apache user');
            //This ensures that carriage returns appear in a textarea
            $utils = $this->app->getUtils();
            $fileError = $utils->fixCarriageReturns($fileError);
            
            if (!$isAdminPage) {
                //Logs the english message
                $message = vsprintf($message, $filePath);
                $this->log($message);
            }
            $fileContents = '';
        }
        return $fileContents;
    }

    /**
     * Gets the information for the txt file
     *
     * @param bool    $getFallbackLang    Whether or not to get the fallback
     *                    language in case that the user or the default
     *                    languages doesn't exist.
     * @param bool    $isAdminPage        Whether or not is the admin page
     * @param string $defaultLang         Default language. If null, it will
     *                   be gotten from the app's config
     *
     * @return  array   An array with the file information
     */
    public function getTxtFileData($getFallbackLang = true,
                        $isAdminPage = false, $defaultLang = null) {
        return $this->getFileData('txt', $getFallbackLang, $isAdminPage,
                   $defaultLang);
    }

    /**
     * Gets the information for the pdf file
     *
     * @param bool    $getFallbackLang    Whether or not to get the fallback
     *                    language in case that the user or the default
     *                    languages doesn't exist.
     * @param bool    $isAdminPage        Whether or not is the admin page
     * @param string $defaultLang         Default language. If null, it will
     *                   be gotten from the app's config
     *
     * @return  array   An array with the file information
     */
    public function getPdfFileData($getFallbackLang = true,
                        $isAdminPage = false, $defaultLang = null) {
        return $this->getFileData('pdf', $getFallbackLang, $isAdminPage,
                   $defaultLang);
    }
}
