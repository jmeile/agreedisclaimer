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

use OCP\AppFramework\Http\Response;

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

    /** @var ILogger    Logger service */
    private $logger;

    /** @var array    Disclaimer types */
    private $disclaimerTypes;

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

        //Please do not change this format. It will be used to store dates
        //an times. For visualization, modify the translation of the string:
        //'mm/dd/yy' on the respective l10n file
        $container->registerParameter('phpDateFormat', 'm/d/Y');
        $container->registerParameter('datepickerDateFormat', 'mm/dd/yy');
        $container->registerParameter('phpTimeFormat', 'H:i');

        //Please note that the disclaimer types must always have the
        //placeholders: %s1 and %s2, this will be replaced afterwards by an html
        //anchor tag '<a>' if the txtFile property is enabled. You may add your
        //own here, but remember to translate them in the l10n files
        $this->disclaimerTypes = [];
        $this->disclaimerTypes['liability']['name'] = 'Disclaimer of liability';
        $this->disclaimerTypes['liability']['text'] = 'I have read and ' .
            'understood the %s1disclaimer of liability%s2';

        $this->disclaimerTypes['legal']['name'] = 'Legal disclaimer';
        $this->disclaimerTypes['legal']['text'] = 'I have read and ' .
            'understood the %s1legal disclaimer%s2';

        $this->disclaimerTypes['gtc']['name'] = 'General Terms and conditions';
        $this->disclaimerTypes['gtc']['text'] = 'I accept ' .
            'the %s1general terms and conditions%s2';

        $this->disclaimerLayouts = [
            ''          => 'None',
            'top-right' => 'Top-Right corner',
            'top-left'  => 'Top-Left corner',
        ];
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
     * @return string    The value of the specified setting
     */
    private function getProp($propName, $defValue = null) {
        return $this->appConfig->getValue($this->app->getAppName(), $propName,
                                     $defValue);
    }

    /**
     * Sets the specified property from the Application configuration
     *
     * @param string $propName     Name of the property to modify 
     * @param mixed  $propValue    Value to set
     */
    private function setProp($propName, $propValue) {
        $this->appConfig->setValue($this->app->getAppName(), $propName,
                                   $propValue);
    }

    /**
     * Gets the php time format used by the app
     *
     * @return string   The time format used by the app
     */
    public function getPhpTimeFormat() {
        $container = $this->app->getContainer();
        return $container->query('phpTimeFormat');
    }

    /**
     * Gets the php date format used by the app
     *
     * @return string   The date format used by the app
     */
    public function getPhpDateFormat() {
        $container = $this->app->getContainer();
        return $container->query('phpDateFormat');
    }

    /**
     * Gets the datepicker date format used by the app
     *
     * @return string   The date format used by the app
     */
    public function getDatepickerDateFormat() {
        $container = $this->app->getContainer();
        return $container->query('datepickerDateFormat');
    }

    /**
     * Gets the php date and time format used by the app concanated into a
     * string
     *
     * @return string   The date and time format used by the app
     */
    public function getPhpDateTimeFormat() {
        return $this->getPhpDateFormat() . ' ' . $this->getPhpTimeFormat();
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
     * Gets the expiration time for cookies
     *
     * @param bool $saveOnNull    Whether or not to save the expiration time if
     *                 it wasn't set before. This will be set to true only on
     *                 the admin page; for the login page (anonymous access),
     *                 the time won't be saved
     *
     * @return string    The cookie expiration time
     */
    public function getCookieExpTime($saveOnNull = false) {
        $cookieExpTime = $this->getProp('cookieExpTime');
        if ($cookieExpTime === null) {
            $cookieExpTime = ''; 
            if ($saveOnNull) {
                $this->setProp('cookieExpTime', $cookieExpTime);
            }
        }
        return $cookieExpTime;
    }

    /**
     * Gets the expiration time interval for cookies
     *
     * @param bool $saveOnNull    Whether or not to save the expiration time
     *                 interval if it wasn't set before. This will be set to
     *                 true only on the admin page; for the login page
     *                 (anonymous access), the time won't be saved
     *
     * @return string    The cookie expiration time, which can be either:
     *                   'days', 'weeks', 'months', or 'years'
     */
    public function getCookieExpTimeIntv($saveOnNull = false) {
        $cookieExpTimeIntv = $this->getProp('cookieExpTimeIntv');
        if ($cookieExpTimeIntv === null) {
            $cookieExpTimeIntv = '';
            if ($saveOnNull) {
                $this->setProp('cookieExpTimeIntv', $cookieExpTimeIntv);
            }
        }
        return $cookieExpTimeIntv;
    }

    /**
     * Converts the entered date format (datepicker notation) to its php
     * representation
     *
     * @param string $srcFormat    The format to convert
     *
     * @remarks    The entered format has the datepicker notation, ie: dd/mm/yy
     */
    function convertDateFormatToPhp($srcFormat) {
        return str_replace(['dd', 'mm', 'yy'], ['d', 'm', 'Y'], $srcFormat);
    }

    /**
     * Converts the entered date to the specified dateFormat
     *
     * @param string $srcDateStr    The date to convert. It must match the
     *                   entered source format
     * @param string $srcFormat     The date format to convert from
     * @param string $destFormat    The date format to convert to
     *
     *
     * @return string    The converted date string
     *
     * @remarks    The entered formats follow the php notation, ie: 'd/m/Y'.
     */
    function convertDate($srcDateStr, $srcFormat, $destFormat) {
        if (($srcDateStr === '') || ($srcFormat === $destFormat)) {
            return $srcDateStr;
        }
        $srcDate = \DateTime::createFromFormat($srcFormat, $srcDateStr);
        return $srcDate->format($destFormat);
    }

    /**
     * Gets a cookie
     *
     * @param string    $cookieName       Name of the cookie to get
     *
     * @remarks: Please note that I didn't get the ownCloud methods working, so,
     *           I'm using the php way. Before trying this, I wasn't using any
     *           path for the cookie, but it didn't work, so, I added
     *           '/' as path.
     */
    public function getCookie($cookieName) {
        //Fix it: use the ownCloud's methods
        //$container = $this->app->getContainer();
        //$request = $container->query('Request');

        //I was having some issues with owncloud's cookie methods, so I decided
        //to use php syntax
        //$cookieValue = $request->getCookie($cookieName);
        $cookieValue = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName] :
                           null;

        return $cookieValue;
    }

    /**
     * Sets a cookie
     *
     * @param string    $cookieName       Name of the cookie to set
     * @param \DateTime $cookieExpDate    DateTime object with the cookie
     *                      expiration date. Please note that php uses seconds
     *                      since the epoch, but since this is complicated, I
     *                      decided to use DateTime and convert them to that
     *                      format afterwards
     *
     * @remarks: Please note that I didn't get the ownCloud methods working, so,
     *           I'm using the php way. Before trying this, I wasn't using any
     *           path for the cookie, but it didn't work, so, I added
     *           '/' as path.
     */
    public function setCookie($cookieName, $cookieValue, $cookieExpDate) {
        //Fix it: use the ownCloud's methods
        //$response = new Response();
        setcookie($cookieName, $cookieValue, $cookieExpDate->format('U'),
            '/'); 

        //I din't get this working, so, I had to use php cookie functions
        //$response->addCookie($cookieName, $cookieValue, $cookieExpDate);
        //return $response;
    }

    /**
     * Expires a cookie
     *
     * @param string $cookieName    Name of the cookie to expire
     *
     * @remarks: Please note that I didn't get the ownCloud methods working, so,
     *           I'm using the php way. Before trying this, I wasn't using any
     *           path for the cookie, but it didn't work, so, I added
     *           '/' as path.
     */
    public function expireCookie($cookieName) {
        //Fix it: use the ownCloud's methods
        //I didn't got this working, so using php functions instead
        /*
        $response = new Response();
        $response->invalidateCookie('AGChecked');
        */
        unset($_COOKIE[$cookieName]);

        //It seems that unsetting it isn't enough, so, we set it with a time in
        //the past
        $yesterday = new \DateTime('now');
        $yesterday->sub(new \DateInterval('P10D'));
        $this->setCookie($cookieName, 'expired', $yesterday);

        //$response->invalidateCookie($cookieName);
        //return $response;
    }


    /**
     * Gets the useCookie application setting
     *
     * @return bool    The value of the useCookie setting 
     */
    public function getUseCookie() {
        $utils = $this->app->getUtils();
        return $utils->strToBool($this->getProp('useCookie', false));
    }

    /**
     * Gets the last visit cookie
     *
     * @return string   A formated string with the date and time of the last
     *                  visit
     */
    public function getLastVisitCookie() {
        $lastVisit = $this->getCookie('AGLastVisit'); 
                    
        if ($lastVisit === null) {
            $lastVisit = $this->setLastVisitCookie(); 
        }
        return $lastVisit;
    }

    /**
     * Sets the last visit cookie
     *
     * @return string   The value to which the last visit cookie was set
     */
    public function setLastVisitCookie() {
        $today = new \DateTime('now');
        $lastVisit = $today->format($this->getPhpDateFormat());
        $farFuture = new \DateTime('2037-01-01');
        $this->setCookie('AGLastVisit', $lastVisit, $farFuture);

        return $lastVisit;
    }

    /**
     * Expires the last visit cookie
     */
    public function expireLastVisitCookie() {
        $this->expireCookie('AGLastVisit');
    }

    /**
     * Gets the value of the cookie indicated whether or not that the disclaimer
     * was accepted
     *
     * @return bool    The boolean value of the cookie
     */
    public function getCheckedCookie() {
        $utils = $this->app->getUtils();

        $forcedExpDateStr = $this->getForcedExpDate();
        if ($forcedExpDateStr !== '') {
            $forcedExpDate = \DateTime::createFromFormat(
                $this->getPhpDateFormat(),
                $forcedExpDateStr);
            $lastVisitCookie = $this->getLastVisitCookie();
            $lastVisitDate = \DateTime::createFromFormat(
                $this->getPhpDateFormat(),
                $lastVisitCookie);

            if ($lastVisitDate <= $forcedExpDate) {
                $this->expireCheckedCookie();
            }
        }
        return $utils->strToBool($this->getCookie('AGChecked'));
    }

    /**
     * Sets the value of the cookie indicated whether or not that the disclaimer
     * was accepted
     */
    public function setCheckedCookie() {
        $cookieExpTime = $this->getCookieExpTime();
        $cookieExpDate = new \DateTime('2037-01-01');
        if ($cookieExpTime !== '') {
            $cookieExpTimeIntv = $this->getCookieExpTimeIntv();
            $cookieExpDate = new \DateTime('now');
            $cookieExpTimeIntvChar = strtoupper(substr($cookieExpTimeIntv,0,1));
            $cookieExpDate->add(new \DateInterval('P' . $cookieExpTime .
                                $cookieExpTimeIntvChar));
            $cookieExpDateStr = $cookieExpDate->format(
                                    $this->getPhpDateTimeFormat()
                                );
        }
        $this->setCookie('AGChecked', true, $cookieExpDate);
    }

    /**
     * Expires the disclaimer cookie
     */
    public function expireCheckedCookie() {
        $this->expireCookie('AGChecked');
    }

    /**
     * Gets the forced expiration date
     */
    public function getForcedExpDate() {
        $forcedExpDate = $this->getProp('forcedExpDate');
        if ($forcedExpDate === null) {
            $forcedExpDate = '';
        }
        return $forcedExpDate;
    }

    /**
     * Gets all the cookie settings
     *
     * @param bool $isAdminPage    Whether or not is the admin page
     *
     * @return array    An array with all cookie settings. It has the format:
     *                  [
     *                      'value' => <using_cookies>,
     *                      'forcedExpDate' => <exp_date_old_cookies>,
     *                      'checkedCookie' => <was_disclaimer_checked>,
     *                      'cookieExpTime' => <exp_time_new_cookies>
     *                      'cookieExpTimeIntv' => <exp_time_intv_new_cookies>
     *                  ]
     *
     *                  Please note that if 'useCookie' is false, no other value
     *                  will be included in the array. Additionally, if the
     *                  method is being called from the login page, then only
     *                  the value of: 'checkedCookie' will be returned; at this
     *                  point no other information is needed. 
     */
    public function getCookieData($isAdminPage = false) {
        $data = [];
        $data['value'] = $this->getUseCookie();
        if ($data['value']) {
            if (!$isAdminPage) {
                $data['checkedCookie'] = $this->getCheckedCookie();
                $saveOnNull = false;
            } else {
                $data['cookieExpTimeIntv'] = $this->getCookieExpTimeIntv(true);
                if ($data['cookieExpTimeIntv'] === '') {
                    $this->setProp('cookieExpTime', '');
                }
                $data['cookieExpTime'] = $this->getCookieExpTime(true);
                if ($data['cookieExpTime'] === '') {
                    $this->setProp('cookieExpTimeIntv', '');
                    $data['cookieExpTimeIntv'] = '';
                }

                $forcedExpDateStr = $this->getForcedExpDate();
                $forcedExpDate = '';
                if ($forcedExpDateStr !== '') {
                    $userDateFormat = $this->l10n->t(
                                          $this->getDatepickerDateFormat()
                                      );
                    $userDateFormat = $this->convertDateFormatToPhp(
                                          $userDateFormat
                                      );

                    $forcedExpDate = $this->convertDate(
                                         $forcedExpDateStr,
                                         $this->getPhpDateFormat(),
                                         $userDateFormat
                                     );
                }
                $data['forcedExpDate'] = $forcedExpDate;
            }
        } elseif ($isAdminPage) {
            $data['forcedExpDate'] = '';
            $data['cookieExpTime'] = '';
            $data['cookieExpTimeIntv'] = ''; 
            $data['forcedExpDate'] = ''; 
        }
        return $data;
    }

    /**
     * Gets the disclaimer texts
     *
     * @return array    The disclaimer texts as an array of the form
     *                  ['<disclaimerType>' => [
     *                          'name' => '<disclaimerName>',
     *                          'text' => '<disclaimerText>'
     *                      ],...
     *                  ]
     */
    public function getDisclaimerTypes() {
        return $this->disclaimerTypes;
    }

    /**
     * Gets the choosen disclaimer text 
     *
     * @return array   The choosen disclaimer text as an array of the form
     *                 ['value' => '<disclaimerType>',
     *                  'name'  => '<disclaimerName>',
     *                  'text'  => '<disclaimerText>'
     *                 ]
     */
    public function getDisclaimerType() {
        $data = [];
        $data['value'] = $this->getProp('disclaimerType', 'liability');
        $data['name'] = $this->disclaimerTypes[$data['value']]['name'];
        $data['text'] = $this->disclaimerTypes[$data['value']]['text'];
        return $data;
    }

    /**
     * Gets the disclaimer position within the user's page
     *
     * @return string   The disclaimer position. Possible values are:
     *      - '': Won't be shown
     *      - 'top-right': Will be shown on the top right corner
     *      - 'top-left': Will be shown on the top left corner
     */
    public function getDisclaimerLayout() {
        return $this->getProp('disclaimerLayout', '');
    }

    /**
     * Gets the different layouts in the user area
     */
    public function getDisclaimerLayouts() {
        return $this->disclaimerLayouts;
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
     * @return array    An array with the file information. It has the
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
