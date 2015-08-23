<?php
/**
* ownCloud - agreedisclaimer
*
* This file is licensed under the MIT License. See the LICENSE file.
*
* @author Josef Meile <technosoftgratis@okidoki.com.co>
* @copyright Josef Meile 2015
*/

/**
* Template that will be rendered on the admin page 
*/
use OCA\AgreeDisclaimer\AppInfo\Application;

$maxAppTxtSizeProp = $_['appName'] . 'maxAppTxtSize';
$maxAdminTxtSizeProp = $_['appName'] . 'maxAdminTxtSize';
$defaultLangProp = $_['appName'] . 'defaultLang';
$txtFileProp = $_['appName'] . 'txtFile';
$txtFilePathProp = $txtFileProp . 'Path';
$txtFileContentsProp = $txtFileProp . 'Contents';
$pdfFileProp = $_['appName'] . 'pdfFile';
$pdfFileUrlProp = $pdfFileProp . 'Url';
$useCookieProp = $_['appName'] . 'useCookie';
$cookieExpTimeProp = $_['appName'] . 'cookieExpTime';
$cookieExpTimeIntvProp = $cookieExpTimeProp . 'Intv';
$forcedExpDateProp = $_['appName'] . 'forcedExpDate';
$datepickerAppFormatProp = $_['appName'] . 'datepickerAppFormat';
$disclaimerTypeProp = $_['appName'] . 'disclaimerType';
$disclaimerLayoutProp = $_['appName'] . 'disclaimerLayout';

/**
 * Adds the utils.js file to the settings page
 */
script($_['appName'], 'utils');

/**
 * Adds the admin.js file to the settings page
 */
script($_['appName'], 'admin');

/**
 * Adds the jquery datepicker locales
 */
script($_['appName'], 'datepicker_l10n');

/**
 * Adds the style sheets file to the settings page
 */
style($_['appName'], 'admin');

?>

<div class="section" id="<?php p($_['appName']); ?>">
    <h2><?php p($l->t('Agree disclaimer')); ?></h2>
    <input id="<?php p($maxAppTxtSizeProp); ?>" type="hidden"
           value="<?php p($_['txtFileData']['maxAppSize']); ?>"/>
    <input id="<?php p($datepickerAppFormatProp); ?>" type="hidden"
           value="<?php p($_['datepickerAppFormat']); ?>"/>
    <label for="<?php p($disclaimerLayoutProp); ?>">
        <?php p($l->t('Menu entry position')); ?>
    </label>&nbsp;
    <select name="<?php p($disclaimerLayoutProp); ?>"
            id="<?php p($disclaimerLayoutProp); ?>">
        <?php 
            foreach($_['disclaimerLayouts'] as $layoutValue
                                          => $layoutText):
        ?>
            <option value="<?php p($layoutValue); ?>" 
                <?php
                    if ($layoutValue == $_['disclaimerLayout']) {
                        p('selected');
                    }
                ?>>
                <?php p($l->t($layoutText)); ?>
            </option>
        <?php endforeach; ?>
    </select><br/>
    <label for="<?php p($defaultLangProp); ?>">
        <?php p($l->t('Default language for the text')); ?>
    </label>&nbsp;
    <select id="<?php p($defaultLangProp); ?>"
        name="<?php p($defaultLangProp); ?>">
        <option value="<?php p($_['currentLang']['code']); ?>">
            <?php p($_['currentLang']['name']); ?>
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
        <b><?php p($_['txtFileData']['basePath'] . '/');
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
    <label for="<?php p($disclaimerTypeProp); ?>">
        <?php p($l->t('Disclaimer type') . ':'); ?>
    </label>&nbsp;
    <select name="<?php p($disclaimerTypeProp); ?>"
            id="<?php p($disclaimerTypeProp); ?>">
        <?php 
            foreach($_['disclaimerTypes'] as $disclaimerValue
                                          => $disclaimerType):
        ?>
            <option value="<?php p($disclaimerValue); ?>" 
                <?php
                    if ($disclaimerValue == $_['textData']['value']) {
                        p('selected');
                    }
                ?>>
                <?php p($l->t($disclaimerType['name'])); ?>
            </option>
        <?php endforeach; ?>
    </select><br/>
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
                      'risk by changing the value of the parameter: ' .
                      'maxAppTxtFileSize inside the config.php file') . '.');
        ?><br/><br/>
    </div>
    <?php p($l->t('Contents of the file')); ?>:<br/>
    <span id="<?php p($txtFilePathProp); ?>">
        <?php p($_['txtFileData']['path']); ?>
    </span>
    <br/>
    <textarea readonly id="<?php p($txtFileContentsProp); ?>"
              class="<?php p($_['appName']); ?>_disabled_input" rows="7"
    ><?php
          if ($_['txtFileData']['error'] !== '') {
              p($_['txtFileData']['error']);
          } else {
              p($_['txtFileData']['contents']);
          }
      ?></textarea>
    <br/>
    <input type="checkbox" id="<?php p($pdfFileProp); ?>"
           name="<?php p($pdfFileProp); ?>"
           <?php if ($_['pdfFileData']['value']) p('checked'); ?>/>
    <label for="<?php p($pdfFileProp); ?>">
        <?php p($l->t('Show a link to a PDF file with the disclaimer')); ?>
    </label><br/>
    <?php p($l->t('Current PDF')); ?>:
    <span id="<?php p($pdfFileUrlProp); ?>">
        <?php
            if ($_['pdfFileData']['error'] !== '') {
                p($_['pdfFileData']['error']);
            } else {
                print_unescaped('<a href="' . $_['pdfFileData']['url'] . '"' .
                    'target="_blank">' . $_['pdfFileData']['name'] . '</a>');
            }
        ?>
    </span>
    <br/><br/>
    <input type="checkbox" id="<?php p($useCookieProp); ?>"
           name="<?php p($useCookieProp); ?>"
           <?php if ($_['cookieData']['value']) p('checked'); ?>/>
    <label for="<?php p($useCookieProp); ?>">
        <?php p($l->t('Use a cookie for remembering the user\'s choice')); ?>
    </label>
    <a id="<?php p($useCookieProp . 'Help'); ?>"
       class="<?php p($_['appName'] . '_help_button'); ?> icon-info svg"
       title="<?php p($l->t('Show/Hide help')); ?>"></a>
    <br/>
    <label for="<?php p($cookieExpTimeProp); ?>">
        <?php p($l->t('Expiration interval for new cookies')); ?>:&nbsp;
    </label>
    <input type="text" id="<?php p($cookieExpTimeProp); ?>"
           name="<?php p($cookieExpTimeProp); ?>"
           value="<?php p($_['cookieData']['cookieExpTime']); ?>"/>
    <select id="<?php p($cookieExpTimeIntvProp); ?>"
            name="<?php p($cookieExpTimeIntvProp); ?>">
        <option value="" 
                <?php 
                    if ($_['cookieData']['cookieExpTimeIntv'] === '') {
                        p('selected');
                }?>>
            <?php p($l->t('Won\'t expire')); ?>
        </option>
        <?php foreach (['Days', 'Weeks', 'Months', 'Years'] as $intv): ?>
            <option value="<?php p(strtolower($intv)) ?>" 
                    <?php 
                        if ($_['cookieData']['cookieExpTimeIntv'] ===
                            strtolower($intv)) {
                            p('selected');
                        }?>>
                 <?php p($l->t($intv)); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br/>
    <label for="<?php p($forcedExpDateProp); ?>">
        <?php p($l->t('Automatically expire cookies older than')); ?>:&nbsp;
    </label>
    <input type="text" id="<?php p($forcedExpDateProp); ?>"
           name="<?php p($forcedExpDateProp); ?>"
           value="<?php p($_['cookieData']['forcedExpDate']); ?>"/>
    <br/>
    <div class="<?php p($_['appName'] . '_help_content'); ?>"
         id="<?php p($useCookieProp . 'HelpContent' ) ?>">
        <?php
            p($l->t('By activating this setting two cookies will be set'). ':');
        ?>
        <ul>
            <li>
                <b>AGChecked:</b> 
                <?php p($l->t('If true, it indicates that the user is a ' .
                        'returning visitor, who already accepted the ' .
                        'disclaimer')); ?>.
            </li>
            <li>
                <b>AGLastVisit:</b>
                <?php p($l->t('Date and time of the last user\'s visit')); ?>.
            </li>
        </ul><br/>
        <?php p($l->t('The value of the AGChecked will be set the first time ' .
                      'that the user visits the website or when it has been ' .
                      'expired')); ?>.<br/><br/>
        <?php p($l->t('On the contrary, if it is a returning user, who ' .
                      'already accepted the disclaimer and there is an '.
                      'expiration time for old cookies, then the age of the ' .
                      'AGChecked cookie will be determined by getting the ' .
                      'AGLastVisit cookie; if it is higher than the '.
                      'expiration date for old cookies, then it will be ' .
                      'invalidated')); ?>.
    </div>
</div>
<div id="<?php p($_['appName']); ?>errorDialog"
     title="<?php p($l->t('Agree disclaimer')); ?> App">
    <p>
        <?php p($l->t('You must check at least one option!')); ?>
    </p>
</div>
