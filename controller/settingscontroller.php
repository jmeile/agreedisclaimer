<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCA\AgreeDisclaimer\Utils;

/**
 * Controller to retreive some application settings through ajax
 */
class SettingsController extends Controller {

    private $appConfig;
    private $filePrefix;
    private $l10n;
    private $urlGenerator;
    private $logger;
    private $loggerParameters;
    private $utils;
    private $txtPath;
    private $pdfPath;

    /**
     * Creates an instance to the SettingsController
     * @param IAppConfig
     */
    public function __construct($AppName, IRequest $request,
                                IAppConfig $appConfig, $filePrefix, IL10N $l10n,
                                IURLGenerator $urlGenerator, ILogger $logger,
                                Utils $utils, $pdfPath, $txtPath) {
        parent::__construct($AppName, $request);
        $this->appConfig = $appConfig;
        $this->filePrefix = $filePrefix;
        $this->l10n = $l10n;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
        $this->loggerParameters = ['app' => $AppName];
        $this->utils = $utils;
        $this->txtPath = $txtPath;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Gets the specified settings from the application configuration
     *
     * @param string            $settingName    Name of the setting to get
     * @param mixed             $defaultValue   Default value in case that the
     *        setting isn't found
     * @return mixed    The value of the specified setting
     */
    private function getSetting($settingName, $defaultValue = null)
    {
        return $this->appConfig->getValue($this->appName, $settingName, $defaultValue);
    }

    /**
     * @PublicPage
     *
     * Gets all the application settings
     *
     * @param   bool    $getFileContents    Whether or not to get the contents
     *          of the text file with the disclaimer text
     * @param   bool    $isAdminForm        Whether or not this method is being
     *          called from the admin form. This is used because the method can
     *          be also called from the login page. Here the differences:
     *          - When called from the admin form, no fall back languages will
     *            be used and all the app settings disgregarding its value will
     *            be return
     *          - When called from the login form, fall back languages will be
     *            used and only the enabled settings will be returned
     *
     * @return  array An array with the application settings with the following
     *          format:
     *          [
     *              'pdfIcon': <url_of_pdf_icon_image>,
     *              '<app_id>UserLang': <language_of_current_user>,
     *              //Defined by the FILE_PREFFIX Application static property
     *              '<app_id>FilePreffix': <naming_preffix_for_files>,
     *              'adminSettings': <array_with_app_settings>
     *          ]
     *          'adminSettings' has the following format:
     *          [
     *              '<appId>DefaultLang     => ['value' => <default_app_lang>],
     *              '<appId>MaxTxtFileSize' => ['value' =>
     *                                          <max_txt_file_size_in_mb>],
     *              '<appId>TxtFile'        => [
     *                  'value'    => <true_or_false>,
     *                  'basePath' => <absolute_path_of_txt_file>,
     *                  'file'     => [
     *                      'exists'  => <does_the_file_exist>,
     *                      'lang'    => <file_language_code>,
     *                      'name'    => <file_name>,
     *                      'path'    => <file_location_in_the_file_system>,
     *                      'url'     => <url_to_file_in_web_browser>,
     *                      'content' => <contents_txt_file>,
     *                      'error'   => <error_message>,
     *                  ],
     *              ],
     *              '<appId>PdfFile'        => [
     *                  'value'    => <true_or_false>,
     *                  'basePath' => <absolute_path_of_pdf_file>,
     *                  'file'     => [
     *                      'exists'  => <does_the_file_exist>,
     *                      'lang'    => <file_language_code>,
     *                      'name'    => <file_name>,
     *                      'path'    => <file_location_in_the_file_system>,
     *                      'url'     => <url_to_file_in_web_browser>,
     *                      'error'   => <error_message>,
     *                  ],
     *              ]
     */
    public function getSettings($getFileContents = true, $isAdminForm = false) {
        $data = [];
        $adminSettings = [];

        $appConfig = $this->appConfig;
        $appId = $this->appName;

        $txtFileProp = $appId . 'TxtFile';
        $adminSettings[$txtFileProp] = [];
        $adminSettings[$txtFileProp]['value'] = $this->getSetting($txtFileProp,
            'true', $appConfig);

        $pdfFileProp = $appId . 'PdfFile';
        $adminSettings[$pdfFileProp] = [];
        $adminSettings[$pdfFileProp]['value'] = $this->getSetting($pdfFileProp,
            'true', $appConfig);

        $data['pdfIcon'] = $this->urlGenerator->linkTo($appId,
            'pdf' . DIRECTORY_SEPARATOR . 'icon.png');

        $defaultLangProp = $appId . 'DefaultLang';
        $adminSettings[$defaultLangProp] = [];
        $defaultLang = $this->getSetting($defaultLangProp, 'en', $appConfig);
        $adminSettings[$defaultLangProp]['value'] = $defaultLang;

        $maxTxtFileSizeProp = $appId . 'MaxTxtFileSize';
        $adminSettings[$maxTxtFileSizeProp] = [];
        $maxTxtFileSize = $this->getSetting($maxTxtFileSizeProp, '1',
            $appConfig);
        $adminSettings[$maxTxtFileSizeProp]['value'] = $maxTxtFileSize;

        if (!$isAdminForm) {
            $userLang = $this->l10n->getLanguageCode();
            $getFallbackLang = true;
        } else {
            //For the admin form only the default language is
            //interesting since the disclaimer won't be shown
            $userLang = $defaultLang;

            //Here we aren't interested in falling back to the main languages
            $getFallbackLang = false;
        }
        $data[$appId . 'UserLang'] = $userLang;
        $txtFileBasePath = $this->txtPath;
        $adminSettings[$txtFileProp]['basePath'] = $txtFileBasePath;
        $pdfFileBasePath = $this->pdfPath;
        $adminSettings[$pdfFileProp]['basePath'] = $pdfFileBasePath;

        $data[$appId . 'FilePreffix'] = $this->filePrefix;
        if (($adminSettings[$txtFileProp]['value'] === 'true')
          || $isAdminForm) {
            $fileInfo = $this->getFile($userLang, $defaultLang, $txtFileBasePath,
                'txt', $getFileContents, $maxTxtFileSize, $getFallbackLang);
            $adminSettings[$txtFileProp]['file'] = $fileInfo;
        }

        if (($adminSettings[$pdfFileProp]['value'] === 'true')
          || $isAdminForm) {
            $fileInfo = $this->getFile($userLang, $defaultLang, $pdfFileBasePath,
                'pdf', false, 2, $getFallbackLang);

            $adminSettings[$pdfFileProp]['file'] = $fileInfo;
        }

        $data['adminSettings'] = $adminSettings;
        return $data;
    }

    /**
     * Gets the file information of the specified file in the entered language
     *
     * @param   string  $userLang           Language used by the current user
     * @param   string  $defaultLang        Default language defined in the
     *          application settings
     * @param   string  $basePath           Base path for the file in the file
     *          system
     * @param   string  $fileExt            Extension of the file; it can be:
     *          'txt' or 'pdf'
     * @param   bool    $getContent         Whether or not to get the file
     *          contents; only used for the txt files
     * @param   int     $maxFileSize        Maximum size of the file in
     *          megabytes; only used for the txt files
     * @param   bool    $getFallbackLang    Whether or not to get files for fall
     *          back languages in case that the current and the default
     *          languages aren't found
     *
     * @return array    Array with the file information. It has this format:
     *                  [
     *                      'exists'  => <does_the_file_exist>,
     *                      'lang'    => <file_language_code>,
     *                      'name'    => <file_name>,
     *                      'path'    => <file_location_in_the_file_system>,
     *                      'url'     => <url_to_file_in_web_browser>,
     *                      'content' => <contents_txt_file>,
     *                      'error'   => <error_message>,
     *                  ],
     */
    public function getFile($userLang, $defaultLang, $basePath, $fileExt,
            $getContent = false, $maxFileSize = 2, $getFallbackLang = true) {
        $fileInfo = [];
        $appId = $this->appName;
        $fileName = $this->filePrefix . '_' . $userLang . '.' .
            $fileExt;
        $filePath = $basePath . DIRECTORY_SEPARATOR . $fileName;
        $fileInfo['exists'] = file_exists($filePath);
        $fileInfo['lang'] = $userLang;
        $langFallbacks = $this->utils->getFallbackLang($userLang);
        $errorMsg = '';
        $userLangFile = $filePath;
        if (!$fileInfo['exists']) {
            $errorMsg = $this->l10n->t('%s doesn\'t exist.',
                $userLangFile . '<br/>') . ' ' .
                $this->l10n->t('Please contact the webmaster');

            $languages = [];
            if ($getFallbackLang) {
                $languages = array_merge($languages, $langFallbacks);
            }
            if ($userLang !== $defaultLang) {
                $languages[] = $defaultLang;
            }
            foreach ($languages as $langCode) {
                $fileName = $this->filePrefix . '_' .
                    $langCode . '.' . $fileExt;
                $filePath = $basePath . DIRECTORY_SEPARATOR . $fileName;
                $fileInfo['exists'] = file_exists($filePath);
                if ($fileInfo['exists']) {
                    $fileInfo['lang'] = $langCode;
                    break;
                }
            }
        }

        $fileInfo['path'] = $filePath;
        $fileInfo['url'] = $this->urlGenerator->linkTo($appId,
            $fileExt . DIRECTORY_SEPARATOR . $fileName);
        $fileInfo['name'] = $fileName;

        $fileInfo['error'] = '';
        if ($getContent && $fileInfo['exists']) {
            $maxBytes = $maxFileSize * 1048576;
            $file_contents = file_get_contents($filePath, false, null, 0,
                $maxBytes);
            if ($file_contents === false) {
                //You have to use === otherwise the empty string will be
                //evaluated to false
                $message = 'Could not read contents from file:\n' . $filePath .
                    '\n\nMake sure that the file exists and that it is ' .
                    'readable by the apache user';

                //This ensures that carriage returns appear in a textarea
                $message = $this->utils->fixCarriageReturns($message);
                $this->logger->error($message, $this->loggerParameters);
                $file_contents = '';
                $fileInfo['error'] = $message;
            }
            $fileInfo['content'] = $file_contents;
        } elseif (!$fileInfo['exists']) {
            if ($userLang !== $defaultLang) {
                $errorMsg = $this->l10n->t('Neither the file:' .
                    ' %s nor: %s exist',
                    ['<br/>'. $userLangFile. '<br/><br/>',
                     '<br/>' . $filePath . '<br/><br/>']) . '. ' .
                    $this->l10n->t('Please contact the ' .
                    'webmaster');
            }
            //This ensures that carriage returns appear in a textarea
            $errorMsg = $this->utils->fixCarriageReturns($errorMsg);
            $fileInfo['error'] = $errorMsg;
        }
        return $fileInfo;
    }

    /**
     * Get the file information for the txt and pdf files
     *
     * @param   bool    $isAdminForm    Whether or not is called from the admin
     *          form. This is used because the method can be also called from
     *          the login page. Here the differences:
     *          - When called from the admin form, no fall back languages will
     *            be used
     *          - When called from the login form, fall back languages will be
     *            used
     * @param   string  $defaultLang    Default language for which the file will
     *          be recovered in case that it doesn't exist for the current
     *          language. In case that it is null, then the default language of
     *          the application will be used.
     *
     * @return array   Array with the file information for the txt and pdf
     *         files. It has this format:
     *          [
     *              '<appId>TxtFile'        => [
     *                  'value'    => <true_or_false>,
     *                  'basePath' => <absolute_path_of_txt_file>,
     *                  'file'     => [
     *                      'exists'  => <does_the_file_exist>,
     *                      'lang'    => <file_language_code>,
     *                      'name'    => <file_name>,
     *                      'path'    => <file_location_in_the_file_system>,
     *                      'url'     => <url_to_file_in_web_browser>,
     *                      'content' => <contents_txt_file>,
     *                      'error'   => <error_message>,
     *                  ],
     *              ],
     *              '<appId>PdfFile'        => [
     *                  'value'    => <true_or_false>,
     *                  'basePath' => <absolute_path_of_pdf_file>,
     *                  'file'     => [
     *                      'exists'  => <does_the_file_exist>,
     *                      'lang'    => <file_language_code>,
     *                      'name'    => <file_name>,
     *                      'path'    => <file_location_in_the_file_system>,
     *                      'url'     => <url_to_file_in_web_browser>,
     *                      'error'   => <error_message>,
     *                  ],
     *              ]
     *          ]
     */
    function getFiles($isAdminForm = false, $defaultLang = null) {
        $data = [];

        if ($defaultLang === null) {
            $defaultLangProp = $this->appName . 'DefaultLang';
            $defaultLang = $this->getSetting($defaultLangProp, 'en');
        }

        if (!$isAdminForm) {
            $userLang = $this->l10n->getLanguageCode();
            $getFallbackLang = true;
        } else {
            $userLang = $defaultLang;
            $getFallbackLang = false;
        }

        $maxTxtFileSizeProp = $this->appName . 'MaxTxtFileSize';
        $maxTxtFileSize = $this->getSetting($maxTxtFileSizeProp, '1');
        $txtFileBasePath = $this->txtPath;
        $pdfFileBasePath = $this->pdfPath;

        $fileInfo = $this->getFile($userLang, $defaultLang, $txtFileBasePath,
            'txt', true, $maxTxtFileSize, $getFallbackLang);
        $data['txtFile'] = $fileInfo;

        $fileInfo = $this->getFile($userLang, $defaultLang, $pdfFileBasePath,
            'pdf', false, 2, $getFallbackLang);
        $data['pdfFile'] = $fileInfo;
        return $data;
    }
}
