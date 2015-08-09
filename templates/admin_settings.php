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
use OCA\AgreeDisclaimer\AppInfo\Application;

$maxAppTxtSizeProp = $_['appName'] . 'MaxAppTxtSize';
$maxAdminTxtSizeProp = $_['appName'] . 'MaxAdminTxtSize';
$defaultLangProp = $_['appName'] . 'DefaultLang';
$txtFileProp = $_['appName'] . 'TxtFile';
$txtFilePathProp = $txtFileProp . 'Path';
$txtFileContentsProp = $txtFileProp . 'Contents';
$pdfFileProp = $_['appName'] . 'PdfFile';
$pdfFileUrlProp = $pdfFileProp . 'Url';

/**
* Adds the admin.js file to the settings page
*/
script($_['appName'], 'admin_settings');

/**
* Adds the style sheets file to the settings page
*/
style($_['appName'], 'admin_settings');
?>

<div class="section" id="<?php p($_['appName']); ?>">
<h2><?php p($l->t('Agree disclaimer')); ?></h2>
<input id="appName" type="hidden"
       value="<?php p($_['appName']); ?>"/>
<input id="<?php p($maxAppTxtSizeProp); ?>" type="hidden"
       value="<?php p($_['maxAppSize']); ?>"/>
<label for="<?php p($defaultLangProp); ?>">
    <?php p($l->t('Default language for the text')); ?>
</label>&nbsp;
<select id="<?php p($defaultLangProp); ?>"
    name="<?php p($defaultLangProp); ?>">
    <option value="<?php p($_['currentLanguage']['code']); ?>">
        <?php p($_['currentLanguage']['name']); ?>
    </option>
    <?php foreach($_['commonLanguages'] as $language): ?>
        <option value="<?php p($language['code']); ?>">
            <?php p($language['name']); ?>
        </option>
    <?php endforeach; ?>
    <optgroup label="––––––––––"></optgroup>
    <?php foreach($_['availableLanguages'] as $language): ?>
        <option value="<?php p($language['code']); ?>">
            <?php p($language['name']); ?>
        </option>
    <?php endforeach; ?>
</select><a id="<?php p($defaultLangProp . 'Help'); ?>"
        class="<?php p($_['appName'] . '_help_button'); ?> icon-info svg"
        title="<?php p($l->t('Show/Hide help')); ?>"></a><br/>
<div class="<?php p($_['appName'] . '_help_content'); ?>"
     id="<?php p($defaultLangProp . 'HelpContent' ) ?>">
    <?php p($l->t('To enable more languages, put your text files '.
                  'here')); ?>:
    <br/>
    <b><?php p($_['txtFileData  ']['basePath'] . '/');
         p($_['filePreffix']); ?>_&lt;lang_code&gt;.txt</b><br/>
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
    <b><?php p($_['pdfFileData']['basePath'] . '/');
             p($_['filePreffix']); ?>_&lt;lang_code&gt;.pdf</b>
    <br/><br/>
    <?php p($l->t('If the file for the user language doesn\'t exist, ' .
            'then the file for the default language will be used')); ?>. 
        <?php p($l->t('In case that the file for the default language ' .
                'doesn\'t exist, then the user will see an error message ' .
                'when trying to read the text')); ?>.<br/><br/>
    </div>
    <input type="checkbox" id="<?php p($txtFileProp); ?>"
           name="<?php p($txtFileProp); ?>"
           <?php if ($_['txtFileData']['value']) p('checked'); ?>/>
    <label for="<?php p($txtFileProp); ?>">
        <?php p($l->t('Show a link to the disclaimer text')); ?>
    </label><br/>

    <label for="<?php p($maxAdminTxtSizeProp); ?>">
        <?php p($l->t('Maximum file size in megabytes for the text file'));?>:
        &nbsp;
    </label>
    <input type="text" id="<?php p($maxAdminTxtSizeProp); ?>"
           name="<?php p($maxAdminTxtSizeProp); ?>"
           value="<?php p($_['txtFileData']['maxAdminSize']); ?>"/>
    <a id="<?php p($maxAdminTxtSizeProp . 'Help'); ?>"
       class="<?php p($_['appName'] . '_help_button'); ?> icon-info svg"
       title="<?php p($l->t('Show/Hide help')); ?>"></a><br/>
    <div class="<?php p($_['appName'] . '_help_content'); ?>"
         id="<?php p($maxAdminTxtSizeProp . 'HelpContent' ) ?>">
        <?php p($l->t('This must be set in order to prevent the download of ' .
                      'big text files, which may block the login page') . '. ');
              p($l->t('If the text file is bigger than this size, then its ' .
                      'contents won\'t appear completely in the dialog, so, ' .
                      'adjust the size till it is adequate to your needs') .
                      '. ');
              p($l->t('Please also note that there is a hard coded limit of: ' .
                      '%s Megabytes; this limit was set in order to prevent ' .
                      'setting higher values that will make ownCloud to ' .
                      'crash', $_['txtFileData']['maxAppSize']) . '. ');
              p($l->t('If you want to modify this limit, do it at your own ' .
                      'risk by changing the value of FILE_SIZE_LIMIT inside '.
                      'the Application.php file') . '.');
        ?><br/><br/>
    </div>
    <?php p($l->t('Contents of the file')); ?>:<br/>
    <span id="<?php p($txtFilePathProp); ?>">
        <?php p($_['txtFileData']['path']); ?>
    </span>
    <br/>
    <textarea readonly id="<?php p($txtFileContentsProp); ?>"
              class="<?php p($_['appName']); ?>_disabled_input" rows="7"
    ><?php p($_['txtFileData']['contents']); ?></textarea>
    <br/>
    <input type="checkbox" id="<?php p($pdfFileProp); ?>"
           name="<?php p($pdfFileProp); ?>"
           <?php if ($_['pdfData']['value']) p('checked'); ?>/>
    <label for="<?php p($pdfFileProp); ?>">
        <?php p($l->t('Show a link to a PDF file with the disclaimer')); ?>
    </label><br/>
    <?php p($l->t('Current PDF')); ?>:
    <span id="<?php p($pdfFileUrlProp); ?>">
        <?php print_unescaped($_['pdfFileData']['url']); ?>
    </span>
</div>
<div id="<?php p($_['appName']); ?>ErrorDialog"
     title="<?php p($l->t('Agree disclaimer')); ?> App">
    <p>
        <?php p($l->t('You must check at least one option!')); ?>
    </p>
</div>
