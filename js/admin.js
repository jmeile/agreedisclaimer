/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Script to setup the html elements of the admin template
 */
$(document).ready(function() {
    'use strict';
    /** Fix it: It would be nice to adquire it from somewhere */
    var appName = 'agreedisclaimer';

    //Application settings
    var pdfFileProp = appName + 'pdfFile';
    var pdfFileUrlProp = pdfFileProp + 'Url';
    var txtFileProp = appName + 'txtFile';
    var txtFileContentsProp = txtFileProp + 'Contents';
    var txtFilePathProp = txtFileProp + 'Path';
    var maxAdminTxtSizeProp = appName + 'maxAdminTxtSize';
    var maxAppTxtSize = parseInt(
                            $('#' + appName + 'maxAppTxtSize').val()
                        );
    var defaultLangProp = appName + 'defaultLang';
    var useCookieProp = appName + 'useCookie';
    var cookieExpTimeProp = appName + 'cookieExpTime';
    var cookieExpTimeIntvProp = cookieExpTimeProp + 'Intv';
    var forcedExpDateProp = appName + 'forcedExpDate';
    var disclaimerTypeProp = appName + 'disclaimerType';


    var userLang = OC.getLocale().substr(0,2);
    initDatePickerLocale(appName, userLang);

    /**
     * Catches the 'change' event of the '<appName>disclaimerType' select
     */
    $('#' + disclaimerTypeProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'disclaimerType', propValue);
    });

    var datepickerUserFormat = $.datepicker.regional[userLang]['dateFormat'];
    var datepickerAppFormat = $('#' + appName + 'datepickerAppFormat').val();

    /**
     * Enables or disables cookie input elements according to the status of the
     * checkbox: useCookieProp
     */
    function enableDisableCookieInputs() {
        var useCookie = $('#' + useCookieProp).attr('checked') ? true : false;
        if (useCookie) {
            $('#' + cookieExpTimeIntvProp).removeAttr('disabled');
            $('#' + forcedExpDateProp).removeAttr('disabled');
        } else {
            $('#' + cookieExpTimeProp).attr('disabled', true);
            $('#' + cookieExpTimeIntvProp).attr('disabled', true);
            $('#' + forcedExpDateProp).attr('disabled', true);

            //Resets previous values
            OC.AppConfig.setValue(appName, 'cookieExpTime', '');
            $('#' + cookieExpTimeProp).val('');
            OC.AppConfig.setValue(appName, 'cookieExpTimeIntv', '');
            $('#' + cookieExpTimeIntvProp).val('');
            OC.AppConfig.setValue(appName, 'forcedExpDate', '');
            $('#' + forcedExpDateProp).val('');
        }
    }

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>useCookie checkbox.
     *
     * @remarks: Note that if this setting is unchecked, the controls:
     *           <appName>cookieExpTime, <appName>cookieExpTimeIntv and
     *           <appName>forcedExpDate will be disabled; otherwise they will be
     *           enabled
     */
    enableDisableCookieInputs();
    $('#' + useCookieProp).on('change', function() {
        enableDisableCookieInputs();
        var propValue = $(this).attr('checked') ? true : false;
        OC.AppConfig.setValue(appName, 'useCookie', propValue);
    });

    /**
     * Only allows digits for the '<appName>cookieExpTime' input
     */
    $('#' + cookieExpTimeProp).keydown(onlyDigits);

    /**
     * Catches the 'change' event of the '<appName>cookieExpTime' input
     */
    $('#' + cookieExpTimeProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'cookieExpTime', propValue);
    });

    /**
     * Catches the 'change' event of the '<appName>cookieExpTimeIntv' select
     */
    $('#' + cookieExpTimeIntvProp).on('change', function(){
        var propValue = $(this).val(); 
        OC.AppConfig.setValue(appName, 'cookieExpTimeIntv', propValue);
        if (propValue === '') {
            OC.AppConfig.setValue(appName, 'cookieExpTime', '');
            $('#' + cookieExpTimeProp).val('');
            $('#' + cookieExpTimeProp).attr('disabled', true);
        } else {
            $('#' + cookieExpTimeProp).removeAttr('disabled');
        }
    });
    if ($('#' + cookieExpTimeIntvProp).val() === '') {
        $('#' + cookieExpTimeProp).attr('disabled', true);
    }

    /**
     * Setups the specified datepicker to have:
     * - The locale corresponding to the user's language
     * - The month select list
     * - The year select list
     * - The 'today' and 'done' buttons (button panel)
     * - The input text disabled so that the user isn't able to enter an invalid
     *   date; only the backspace and delete keys will be active and they will
     *   delete the whole text input
     * - Respond to the 'change' method and save the date to the app settings
     *
     * @param string id          Id of the date picker to setup
     * @param string userLang    Current user language
     */
    function configDatepicker(id, userLang) {
        var propValue = $('#' + id).val();

        $('#' + id).datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            minDate: new Date(2015, 0, 1),
            maxDate: new Date(), 
            onClose: function(selectedDate) {
                var propValue = $(this).val();
                var propName = $(this).attr('id').split(appName)[1];
                //Do not change the date format here. It is the one using to
                //storing dates
                var convertedDate = convertDate(propValue,
                        datepickerUserFormat, datepickerAppFormat);
                OC.AppConfig.setValue(appName, propName, convertedDate);
            }
        });

        //Changes the jquery datepicker locale
        $('#' + id).datepicker('option',
            $.datepicker.regional[userLang]
        );

        /** 
          * Disables all keys on the datepicker input texts in order to prevent
          * that the user enters an invalid key. Only the 'Backspace' and
          * 'Delete' keys are allowed; when pressed, the whole input text will
          * be deleted
          */
        $('#' + id).keydown(function (e) {
            // Allow the Windows and MAC key, Alt
            if ((e.metaKey === true) || (e.altKey === true) ||
            // Allow: F1 till F12 
                (e.keyCode >= 112 && e.keyCode <= 123)) {
                return;
            }

            if ($.inArray(e.keyCode, [46, 8]) !== -1) {
                var propName = $(this).attr('id').split(appName)[1];
                $(this).val('');
                OC.AppConfig.setValue(appName, propName, '');
            }
            e.preventDefault();
            return;
        });
        $('#' + id).val(propValue);
    }

    configDatepicker(forcedExpDateProp, userLang);

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>txtFile checkbox.
     */
    $('#' + txtFileProp).on('change', function() {
        forceTxtOrPdf($(this), $('#' + pdfFileProp));
    });

    /**
     * Enables/Disables the '<appName>maxAdminTxtSize' text field on load
     * acording to the value of the <appName>txtFile setting
     */
    var isTxtFilePropChecked = $('#' + txtFileProp).attr('checked') ? true :
        false;
    if (!isTxtFilePropChecked) {
        $('#' + maxAdminTxtSizeProp).attr('disabled', true);
    }

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>pdfFile checkbox.
     */
    $('#' + pdfFileProp).on('change', function(){
        forceTxtOrPdf($(this), $('#' + txtFileProp));
    });

    /**
     * Forces that at least one of the entered checkboxes is set
     *
     * @param string control1   Control were the 'change' event was triggered
     * @param string control2   Second control that will be validated
     */
    function forceTxtOrPdf(control1, control2) {
        var propValue1 = control1.attr('checked') ? true : false;
        var propValue2 = control2.attr('checked') ? true : false;
        if ( !propValue1 && !propValue2 ) {
            $('#' + appName + 'errorDialog').dialog('open');
            propValue1 = true;
            control1.attr('checked', true);
        }
        var propName = control1.attr('id').split(appName)[1];
        OC.AppConfig.setValue(appName, propName, propValue1);
    }

    /**
     * Shows the contents of the txt file and a link to the pdf for the selected
     * language. If the text file doesn't exist, then an error message will be
     * displayed on the textarea that is supposed to show its contents. For the
     * pdfs, an error will be displayed instead of a link.
     */
    $('#' + defaultLangProp).on('change', function(){
        var propValue = $(this).val();
        OC.AppConfig.setValue(appName, 'defaultLang', propValue);
        reloadFileInfo(propValue);
    });

    /**
     * Reloads the txt file contents and the pdf link according to the entered
     * language by calling the settings#get_files route through an ajax request
     *
     * @param string currentLang    The current language
     */
    function reloadFileInfo(currentLang)
    {
        var baseUrl = OC.generateUrl('/apps/' + appName +
                '/settings/get_files');
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
                    $('#' + txtFileContentsProp).html(
                        files['txtFileData']['contents']
                     );
                } else {
                    $('#' + txtFileContentsProp).html(
                        files['txtFileData']['error']
                     );
                }
                $('#' + txtFilePathProp).html(files['txtFileData']['path']);

                if (files['pdfFileData']['error'] === '') {
                    var pdfLink = files['pdfFileData']['url'];
                    pdfLink = '<a href="' + pdfLink + '" target="_blank">' +
                        files['pdfFileData']['name'] +'</a>';
                    $('#' + pdfFileUrlProp).html(pdfLink);
                } else {
                    $('#' + pdfFileUrlProp).html(files['pdfFileData']['error']);
                }
            }
        });
    }

    /**
     * Catches the 'change' event of the '<appName>maxAdminTxtSize' input in
     * order to warranted that it has to be lower than maxAppTxtFileSize
     */
    $('#' + maxAdminTxtSizeProp).on('change', function(){
        var propValue = $(this).val(); 
        propValue = parseInt(propValue);
        if (propValue > maxAppTxtSize) {
            propValue = maxAppTxtSize;
            $(this).val(maxAppTxtSize);
        } else if ((propValue === 0) || isNaN(propValue)) {
            propValue = 1;
            $(this).val(1);
        }
        OC.AppConfig.setValue(appName, 'maxAdminTxtSize', propValue);
        reloadFileInfo($('#' + defaultLangProp).val());
    });

    /**
     * Only allows digits for text inputs
     *
     * @remarks: This code was taken from (some modifications were done):
     * - How to allow only numeric (0-9) in HTML inputbox using jQuery?
     *   Answer by SpYk3HH
     *   http://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
     */
    function onlyDigits(e) {
        if (
            // Allow: backspace, delete, tab, escape, enter, and scroll-lock
            ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 145]) !== -1) ||
            // Allow the Windows and MAC key, Alt
            (e.metaKey === true) || (e.altKey === true) ||
            // Allow: F1 till F12 
            (e.keyCode >= 112 && e.keyCode <= 123) || 
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }

        if  ( e.ctrlKey === true )
        {
            // Prevents the usage of Ctrl+V. Here the user could paste something
            // that isn't a number, so instead of doing an extra validation,
            // this will be disabled.
            if (e.keyCode == 86) {
                e.preventDefault();
            }
            return;
        }

        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57))
         && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    }

    /**
     * Only allows digits for the '<appName>maxAdminFileSize' input
     */
    $('#' + maxAdminTxtSizeProp).keydown(onlyDigits);

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
