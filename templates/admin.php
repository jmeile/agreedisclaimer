<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Template that will be rendered on the admin page 
 */

use \OCA\AgreeDisclaimer\Controller\SettingsController;
use \OCA\AgreeDisclaimer\AppInfo\Application;

//Gets the application settings
$appId = Application::APP_ID;
$data = SettingsController::getSettings(true, true);
$adminSettings = $data['adminSettings'];
$txtFileProp = $appId . 'TxtFile';
$txtFile = $adminSettings[$txtFileProp]['value'];

$txtFileContentsProp = $txtFileProp . 'Contents';
$txtFilePathProp = $txtFileProp . 'Path';
if ($adminSettings[$txtFileProp]['file']['error'] === '') {
    //If there isn't any error with the txt file, then its contents will be
    //retreived
    $textAreaContents = $adminSettings[$txtFileProp]['file']['content'];
} else {
    //Otherwise, an error will be displayed
    $textAreaContents = $adminSettings[$txtFileProp]['file']['error'];
}

$pdfFileProp = $appId . 'PdfFile';
$pdfFile = $adminSettings[$pdfFileProp]['value'];
$pdfFileUrlProp = $pdfFileProp . 'Url'; 
if ($adminSettings[$pdfFileProp]['file']['error'] === '') {
    //If there isn't any error with the pdf file, then a link to it will be
    //shown 
    $pdfLink = $adminSettings[$pdfFileProp]['file']['url'];
    $pdfLink = '<a href="' . $pdfLink . '" target="_blank">' .
        $adminSettings[$pdfFileProp]['file']['name'] .'</a>';
} else {
    //Otherwise, an error will be displayed
    $pdfLink = $adminSettings[$pdfFileProp]['file']['error'];
}

$maxTxtFileSizeProp = $appId . 'MaxTxtFileSize';
$defaultLangProp = $appId . 'DefaultLang';
$maxFileSizeLimitProp = $appId . 'MaxFileSizeLimit';
$maxFileSizeLimit = Application::FILE_SIZE_LIMIT;


//The current administrator's language and the ownCloud supported language will
//be retreived
$localeInfo = Application::getAvailableLanguages(
                $adminSettings[$defaultLangProp]['value']);
$currentLanguage = $localeInfo['activelanguage'];
$commonLanguages = $localeInfo['commonlanguages'];
$availableLanguages = $localeInfo['languages'];


/**
 * Adds the admin.js file to the settings page
 */
script($appId, 'admin');

/**
 * Adds the style sheets file to the settings page
 */
style($appId, 'admin');
?>

<div class="section" id="<?php p($appId); ?>">
    <h2><?php p($l->t('Agree disclaimer')); ?></h2>
    <input id="<?php p($maxFileSizeLimitProp); ?>" type="hidden"
           value="<?php p($maxFileSizeLimit); ?>"/>
    <label for="<?php p($defaultLangProp); ?>">
        <?php p($l->t('Default language for the text')); ?>
    </label>&nbsp;
    <select id="<?php p($defaultLangProp); ?>"
        name="<?php p($defaultLangProp); ?>">
        <option value="<?php p($currentLanguage['code']); ?>">
            <?php p($currentLanguage['name']); ?>
        </option>
        <?php foreach($commonLanguages as $language): ?>
            <option value="<?php p($language['code']); ?>">
                <?php p($language['name']); ?>
            </option>
        <?php endforeach; ?>
        <optgroup label="––––––––––"></optgroup>
        <?php foreach($availableLanguages as $language): ?>
            <option value="<?php p($language['code']); ?>">
                <?php p($language['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><a id="<?php p($defaultLangProp . 'Help'); ?>"
            class="<?php p($appId . '_help_button'); ?> icon-info svg"
            title="<?php p($l->t('Show/Hide help')); ?>"></a><br/>
    <div class="<?php p($appId . '_help_content'); ?>"
         id="<?php p($defaultLangProp . 'HelpContent' ) ?>">
        <?php p($l->t('To enable more languages, put your text files '.
                      'here')); ?>:
        <br/>
        <b><?php p($adminSettings[$appId . 'TxtFile']['basePath'] . '/');
             p($data[$appId . 'FilePreffix']); ?>_&lt;lang_code&gt;.txt</b><br/>
        <?php p($l->t('Where')); ?>:<br/>
        <ul>
            <li>
                <?php print_unescaped($l->t('%s is the language code for the ' .
                    'respective disclaimer translation',
                    '<b>&lt;lang_code&gt;:</b>')); ?>. 
                <?php print_unescaped($l->t('It can be a two character code, ' .
                    'ie: %s or a five character code, ie: %s',
                    array("<i>'en'</i>", "<i>'de_CH'</i>"))); ?>
            </li>
        </ul><br/>
        <?php p($l->t('Likewise put your pdf files under')); ?>:<br/>
        <b><?php p($adminSettings[$appId . 'PdfFile']['basePath'] . '/');
                 p($data[$appId . 'FilePreffix']); ?>_&lt;lang_code&gt;.pdf</b>
        <br/><br/>
        <?php p($l->t('If the file for the user language doesn\'t exist, ' .
                'then the file for the default language will be used')); ?>. 
        <?php p($l->t('In case that the file for the default language ' .
                'doesn\'t exist, then the user will see an error message ' .
                'when trying to read the text')); ?>.<br/><br/>
    </div>
    <input type="checkbox" id="<?php p($txtFileProp); ?>"
           name="<?php p($txtFileProp); ?>"
           <?php if ($txtFile === 'true') p('checked'); ?>/>
    <label for="<?php p($txtFileProp); ?>">
        <?php p($l->t('Show a link to the disclaimer text')); ?>
    </label><br/>

    <label for="<?php p($maxTxtFileSizeProp); ?>">
        <?php p($l->t('Maximum file size in megabytes for the text file'));?>:
        &nbsp;
    </label>
    <input type="text" id="<?php p($maxTxtFileSizeProp); ?>"
           name="<?php p($maxTxtFileSizeProp); ?>"
           value="<?php p($adminSettings[$maxTxtFileSizeProp]['value']); ?>"/>
    <a id="<?php p($maxTxtFileSizeProp . 'Help'); ?>"
       class="<?php p($appId . '_help_button'); ?> icon-info svg"
       title="<?php p($l->t('Show/Hide help')); ?>"></a><br/>
    <div class="<?php p($appId . '_help_content'); ?>"
         id="<?php p($maxTxtFileSizeProp . 'HelpContent' ) ?>">
        <?php p($l->t('This must be set in order to prevent the download of ' .
                      'big text files, which may block the login page') . '. ');
              p($l->t('If the text file is bigger than this size, then its ' .
                      'contents won\'t appear completely in the dialog, so, ' .
                      'adjust the size till it is adequate to your needs') .
                      '. ');
              p($l->t('Please also note that there is a hard coded limit of: ' .
                      '%s Megabytes; this limit was set in order to prevent ' .
                      'setting higher values that will make ownCloud to ' .
                      'crash', $maxFileSizeLimit) . '. ');
              p($l->t('If you want to modify this limit, do it at your own ' .
                      'risk by changing the value of FILE_SIZE_LIMIT inside '.
                      'the Application.php file') . '.');
        ?><br/><br/>
    </div>
    <?php p($l->t('Contents of the file')); ?>:<br/>
    <span id="<?php p($txtFilePathProp); ?>">
        <?php p($adminSettings[$txtFileProp]['file']['path']); ?>
    </span>
    <br/>
    <textarea readonly id="<?php p($txtFileContentsProp); ?>"
              class="<?php p($appId); ?>_disabled_input" rows="7"
    ><?php p($textAreaContents); ?></textarea>
    <br/>
    <input type="checkbox" id="<?php p($pdfFileProp); ?>"
           name="<?php p($pdfFileProp); ?>"
           <?php if ($pdfFile === 'true') p('checked'); ?>/>
    <label for="<?php p($pdfFileProp); ?>">
        <?php p($l->t('Show a link to a PDF file with the disclaimer')); ?>
    </label><br/>
    <?php p($l->t('Current PDF')); ?>:
    <span id="<?php p($pdfFileUrlProp); ?>">
        <?php print_unescaped($pdfLink); ?>
    </span>
</div>
<div id="<?php p($appId); ?>ErrorDialog"
     title="<?php p($l->t('Agree disclaimer')); ?> App">
    <p>
        <?php p($l->t('You must check at least one option!')); ?>
    </p>
</div>
