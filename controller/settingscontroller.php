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

    private $l10n;
    private $config;

    /**
     * Creates an instance to the SettingsController 
     */
    public function __construct($AppName, IRequest $request, IL10N $l10n,
                        Config $config) {
        parent::__construct($AppName, $request);
        $this->l10n = $l10n;
        $this->config = $config;
    }

    /**
     * @PublicPage
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
}
