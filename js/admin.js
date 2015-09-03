/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

var AgreeDisclaimer = AgreeDisclaimer || {};

/**
 * Config class to read and write the application settings from the admin page 
 */
(function(window, $, exports, undefined) {
    'use strict';

    /**
     * Creates a Config object
     *
     * @param string                          appName
     *            Application's name
     * @param string                          userLang
     *            Current user language
     * @param AgreeDisclaimer.Utils           Utils
     *            Utils object to access several utility functions
     * @param AgreeDisclaimer.DatepickerUtils DatepickerUtils
     *            Datepicker object to setup the jquery datepicker widgets
     */
    var Config = function(appName, userLang, Utils, DatepickerUtils) {
        this.appName = appName;
        this.userLang = userLang;
        this.utils = Utils;
        this.datepickerUtils = DatepickerUtils;
    };

    /**
     * Initializes the Config object
     */
    Config.prototype.init = function() {
        this.pdfFileProp = this.appName + 'pdfFile';
        this.pdfFileUrlProp = this.pdfFileProp + 'Url';
        this.txtFileProp = this.appName + 'txtFile';
        this.txtFileContentsProp = this.txtFileProp + 'Contents';
        this.txtFilePathProp = this.txtFileProp + 'Path';
        this.maxAdminTxtSizeProp = this.appName + 'maxAdminTxtSize';
        this.maxAppTxtSize = parseInt(
                                 $('#' + this.appName + 'maxAppTxtSize').val()
                             );
        this.defaultLangProp = this.appName + 'defaultLang';
        this.useCookieProp = this.appName + 'useCookie';
        this.cookieExpTimeProp = this.appName + 'cookieExpTime';
        this.cookieExpTimeIntvProp = this.cookieExpTimeProp + 'Intv';
        this.forcedExpDateProp = this.appName + 'forcedExpDate';
        this.disclaimerTypeProp = this.appName + 'disclaimerType';
        this.disclaimerLayoutProp = this.appName + 'disclaimerLayout';

        this.datepickerUtils.initDatePickerLocale();
        this.enableDisableCookieInputs();

        if ($('#' + this.cookieExpTimeIntvProp).val() === '') {
            $('#' + this.cookieExpTimeProp).attr('disabled', true);
        }
        this.datepickerUtils.configDatepicker(this.forcedExpDateProp);
    }

    /**
     * Enables or disables cookie input elements according to the status of the
     * checkbox: useCookieProp
     */
    Config.prototype.enableDisableCookieInputs = function() {
        var useCookie = $('#' + this.useCookieProp).attr('checked') ? true : 
                            false;
        if (useCookie) {
            $('#' + this.cookieExpTimeIntvProp).removeAttr('disabled');
            $('#' + this.forcedExpDateProp).removeAttr('disabled');
        } else {
            $('#' + this.cookieExpTimeProp).attr('disabled', true);
            $('#' + this.cookieExpTimeIntvProp).attr('disabled', true);
            $('#' + this.forcedExpDateProp).attr('disabled', true);

            //Resets previous values
            OC.AppConfig.setValue(this.appName, 'cookieExpTime', '');
            $('#' + this.cookieExpTimeProp).val('');
            OC.AppConfig.setValue(this.appName, 'cookieExpTimeIntv', '');
            $('#' + this.cookieExpTimeIntvProp).val('');
            OC.AppConfig.setValue(this.appName, 'forcedExpDate', '');
            $('#' + this.forcedExpDateProp).val('');
        }
    };

    /**
     * Forces that at least one of the entered checkboxes is set
     *
     * @param string control1   Control were the 'change' event was triggered
     * @param string control2   Second control that will be validated
     */
    Config.prototype.forceTxtOrPdf = function(control1, control2) {
        var propValue1 = control1.attr('checked') ? true : false;
        var propValue2 = control2.attr('checked') ? true : false;
        if ( !propValue1 && !propValue2 ) {
            $('#' + this.appName + 'errorDialog').dialog('open');
            propValue1 = true;
            control1.attr('checked', true);
        }
        var propName = control1.attr('id').split(this.appName)[1];
        OC.AppConfig.setValue(this.appName, propName, propValue1);
    };

    /**
     * Reloads the txt file contents and the pdf link according to the entered
     * language by calling the settings#get_files route through an ajax request
     *
     * @param string currentLang    The current language
     */
    Config.prototype.reloadFileInfo = function(currentLang) {
        var baseUrl = OC.generateUrl('/apps/' + this.appName +
                '/settings/get_files');

        //Quick hack to be able to access the 'this' object properties. You can
        //also achieve this by setting the context setting from ajax to 'this',
        //then you can just write "this.prop"; however, if you want to access
        //the ajax object itself, you won't be able to because 'this' won't
        //reffer to it anymore
        var obj = this;

        $.ajax({
            url: baseUrl,
            type: 'GET',
            data: {
                isAdminForm: true,
                defaultLang: currentLang,
            },
            contentType: 'application/json; charset=utf-8',
            success: function(files) {
                if (files['txtFileData']['error'] === '') {
                    $('#' + obj.txtFileContentsProp).text(
                        files['txtFileData']['contents']
                     );
                } else {
                    $('#' + obj.txtFileContentsProp).text(
                        files['txtFileData']['error']
                     );
                }
                $('#' + obj.txtFilePathProp).text(files['txtFileData']['path']);

                if (files['pdfFileData']['error'] === '') {
                    var pdfLink = files['pdfFileData']['url'];
                    pdfLink = '<a href="' + pdfLink + '" target="_blank">' +
                        files['pdfFileData']['name'] +'</a>';
                    $('#' + obj.pdfFileUrlProp).html(pdfLink);
                } else {
                    $('#' + obj.pdfFileUrlProp).text(
                        files['pdfFileData']['error']);
                }
            }
        });
    };

    exports.Config = Config;
})(window, jQuery, AgreeDisclaimer);

$(document).ready(function() {
    /** Fix it: It would be nice to adquire the app's name from somewhere */
    var appName = 'agreedisclaimer';
    var userLang = OC.getLocale().substr(0,2);
    var datepickerAppFormat = $('#' + appName + 'datepickerAppFormat').val();

    var utils = new AgreeDisclaimer.Utils();
    var datepickerUtils = new AgreeDisclaimer.DatepickerUtils(appName, userLang,
                              monthNames, dayNames,
                              datepickerAppFormat, utils);

    var config = new AgreeDisclaimer.Config('agreedisclaimer', userLang, utils,
                     datepickerUtils); 
    config.init();

    /** jquery events */

    /**
     * Catches the 'change' event of the '<appName>disclaimerType' select
     */
    $('#' + config.disclaimerTypeProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'disclaimerType', propValue);
    });

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>useCookie checkbox.
     *
     * @remarks: Note that if this setting is unchecked, the controls:
     *           <appName>cookieExpTime, <appName>cookieExpTimeIntv and
     *           <appName>forcedExpDate will be disabled; otherwise they will be
     *           enabled
     */
    $('#' + config.useCookieProp).on('change', function() {
        config.enableDisableCookieInputs();
        var propValue = $(this).attr('checked') ? true : false;
        OC.AppConfig.setValue(appName, 'useCookie', propValue);
    });

    /**
     * Only allows digits for the '<appName>cookieExpTime' input
     */
    $('#' + config.cookieExpTimeProp).keydown(utils.onlyDigits);

    /**
     * Catches the 'change' event of the '<appName>cookieExpTime' input
     */
    $('#' + config.cookieExpTimeProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'cookieExpTime', propValue);
    });

    /**
     * Catches the 'change' event of the '<appName>cookieExpTimeIntv' select
     */
    $('#' + config.cookieExpTimeIntvProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'cookieExpTimeIntv', propValue);
        if (propValue === '') {
            OC.AppConfig.setValue(appName, 'cookieExpTime', '');
            $('#' + config.cookieExpTimeProp).val('');
            $('#' + config.cookieExpTimeProp).attr('disabled', true);
        } else {
            $('#' + config.cookieExpTimeProp).removeAttr('disabled');
        }
    });

    /**
     * Enables/Disables the '<appName>maxAdminTxtSize' text field on load
     * acording to the value of the <appName>txtFile setting
     */
    var isTxtFilePropChecked = $('#' + config.txtFileProp).attr('checked') ?
        true : false;
    if (!isTxtFilePropChecked) {
        $('#' + config.maxAdminTxtSizeProp).attr('disabled', true);
    }

    /**
     * Catches the 'change' event of the '<appName>maxAdminTxtSize' input in
     * order to warranted that it has to be lower than maxAppTxtFileSize
     */
    $('#' + config.maxAdminTxtSizeProp).on('change', function(){
        var propValue = $(this).val();
        propValue = parseInt(propValue);
        if (propValue > config.maxAppTxtSize) {
            propValue = config.maxAppTxtSize;
            $(this).val(config.maxAppTxtSize);
        } else if ((propValue === 0) || isNaN(propValue)) {
            propValue = 1;
            $(this).val(1);
        }
        OC.AppConfig.setValue(config.appName, 'maxAdminTxtSize', propValue);
        config.reloadFileInfo($('#' + config.defaultLangProp).val());
    });

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>txtFile checkbox.
     */
    $('#' + config.txtFileProp).on('change', function() {
        config.forceTxtOrPdf($(this), $('#' + config.pdfFileProp));
    });

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>pdfFile checkbox.
     */
    $('#' + config.pdfFileProp).on('change', function(){
        config.forceTxtOrPdf($(this), $('#' + config.txtFileProp));
    });

    /**
     * Shows the contents of the txt file and a link to the pdf for the selected
     * language. If the text file doesn't exist, then an error message will be
     * displayed on the textarea that is supposed to show its contents. For the
     * pdfs, an error will be displayed instead of a link.
     */
    $('#' + config.defaultLangProp).on('change', function(){
        var propValue = $(this).val();
        OC.AppConfig.setValue(appName, 'defaultLang', propValue);
        config.reloadFileInfo(propValue);
    });

    /**
     * Changes the disclaimerLayout applicatin settings
     */
    $('#' + config.disclaimerLayoutProp).on('change', function(){
        var propValue = $(this).val();
        OC.AppConfig.setValue(appName, 'disclaimerLayout', propValue);
    });

    /**
     * Only allows digits for the '<appName>maxAdminFileSize' input
     */
    $('#' + config.maxAdminTxtSizeProp).keydown(utils.onlyDigits);

    //Hides the help texts during load
    $('.' + appName + '_help_content').hide();

    //Shows / Hides the help
    $('.' + appName + '_help_button').click(function(e) {
        var divId = $(this).attr('id') + 'Content';
        $('#' + divId).toggle();
        e.preventDefault;
    });

    /**
     * Dialog for showing errors
     */
    $('#' + appName + 'errorDialog').dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        closeText: 'Close',
        buttons: [
            {
                text: t(appName, 'Continue'),
                click: function() {
                    $(this).dialog('close');
                }
            },
        ]
    });
});
