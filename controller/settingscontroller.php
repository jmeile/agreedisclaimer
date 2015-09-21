<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

namespace OCA\AgreeDisclaimer\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;

use OCP\IL10N;

use OCA\AgreeDisclaimer\Config\Config;

/**
 * Controller to retreive some application settings through ajax
 */
class SettingsController extends Controller {

    /** @var IL10N    Translation service */
    private $l10n;

    /** @var Config    Configuration settings */
    private $config;

    /**
     * Creates an instance to the SettingsController 
     *
     * @param string   $AppName    Application's name
     * @param IRequest $request    Application's request
     * @param IL10N    $l10n       Translation service
     * @param Config   $config     Configuration settings
     */
    public function __construct($AppName, IRequest $request, IL10N $l10n,
                        Config $config) {
        parent::__construct($AppName, $request);
        $this->l10n = $l10n;
        $this->config = $config;
    }

    /**
     * @PublicPage
     * Get the file information for the txt and pdf files, the cookie
     * settings, and the disclaimer type
     *
     * @param   bool $isAdminForm      Whether or not is called from the admin
     *          form. This is used because the method can be also called from
     *          the login page. Here the differences:
     *          - When called from the admin form, no fall back languages will
     *            be used 
     *          - When called from the login form, fall back languages will be
     *            used 
     * @param   string $defaultLang    Default language for which the file will
     *          be recovered in case that it doesn't exist for the current
     *          language. In case that it is null, then the default language of
     *          the application will be used.
     *
     * @return array   Array with the file information for the txt and pdf
     *         files. See the Config class methods: getTxtFileData,
     *         getPdfFileData, getCookieData, and getDisclaimerType for more
     *         info about the returned array, which has the format
     *         [
     *             'txtFileData'        => <txt_file_data>,
     *             'pdfFileData'        => <pdf_file_data>,
     *             'cookieDate'         => <cookie_data>,
     *             'textData'           => <text_data>
     *         ]
     */
    function getSettings($isAdminForm = false, $defaultLang = null) {
        $data = $this->getFiles($isAdminForm, $defaultLang);
        $data['cookieData'] = $this->config->getCookieData($isAdminForm);
        $data['textData'] = $this->config->getDisclaimerType(true);
        return $data;
    }

    /**
     * Get the file information for the txt and pdf files
     *
     * @param   bool $isAdminForm      Whether or not is called from the admin
     *          form. This is used because the method can be also called from
     *          the login page. Here the differences:
     *          - When called from the admin form, no fall back languages will
     *            be used 
     *          - When called from the login form, fall back languages will be
     *            used 
     * @param   string $defaultLang    Default language for which the file will
     *          be recovered in case that it doesn't exist for the current
     *          language. In case that it is null, then the default language of
     *          the application will be used.
     *
     * @return array   Array with the file information for the txt and pdf
     *         files. See the Config class methods: getTxtFileData and
     *         getPdfFileData for more info about the returned array, which has
     *         the format
     *         [
     *             'txtFileData'        => <txt_file_data>,
     *             'pdfFileData'        => <pdf_file_data>,
     *         ]
     */
    function getFiles($isAdminForm = false, $defaultLang = null) {
        $data = [];
        if ($defaultLang === null) {
            $defaultLang = $this->config->getDefaultLang();
        }

        if (!$isAdminForm) {
            $userLang = $this->l10n->getLanguageCode();
            $getFallbackLang = true;
        } else {
            $userLang = $defaultLang;
            $getFallbackLang = false;
        }
        $data['txtFileData'] = $this->config->getTxtFileData($getFallbackLang,
                                   $isAdminForm, $defaultLang);
        $data['pdfFileData'] = $this->config->getPdfFileData($getFallbackLang, 
                                   $isAdminForm, $defaultLang);
        return $data;
    }

    /**
     * @NoAdminRequired
     * Gets the disclaimer layout and the file information
     *
     * @return array   Array with the file information for the txt and pdf
     *         files and the disclaimer layout. See the Config class methods:
     *         getTxtFileData, getPdfFileData, and getDisclaimerType for more
     *         info about the returned array, which has the format
     *         [
     *             'txtFileData'        => <txt_file_data>,
     *             'pdfFileData'        => <pdf_file_data>,
     *             'textData'           => <text_data>
     *             'layout'             => <disclaimer_layout>
     *         ]
     */
    function getDisclaimerLayout() {
        $data = [];
        $disclaimerLayout = $this->config->getDisclaimerLayout();
        if ($disclaimerLayout !== '') {
            $data = $this->getFiles();
            $data['textData'] = $this->config->getDisclaimerType(true);
        }
        $data['layout'] = $disclaimerLayout; 
        return $data;
    }
}
