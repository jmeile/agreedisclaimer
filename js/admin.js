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
$(document).ready(function(){
    'use strict';
    /** Fix it: It would be nice to adquire it from somewhere */
    var appName = 'agreedisclaimer'; 

    //Application settings
    var pdfFileProp = appName + 'PdfFile';
    var pdfFileUrlProp = pdfFileProp + 'Url';
    var txtFileProp = appName + 'TxtFile';
    var txtFileContentsProp = txtFileProp + 'Contents';
    var txtFilePathProp = txtFileProp + 'Path';
    var maxAdminTxtSizeProp = appName + 'MaxAdminTxtSize';
    var maxAppTxtSize = parseInt(
                            $('#' + appName + 'MaxAppTxtSize').val()
                        );
    var defaultLangProp = appName + 'DefaultLang';

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>TxtFile checkbox.
     *
     * @remarks: Note that if this setting is unchecked and the <appName>PdfFile
     *           setting is also unchecked, an error dialog will be shown and
     *           the checkbox will be checked again. This is needed to
     *           warranted that at least one option is shown on the login page:
     *           either a link to the txt file contents, a link to a pdf file,
     *           or both.
     */
    $('#' + txtFileProp).on('change', function(){
        var propValue1 = $(this).attr('checked') ? true : false;
        var propValue2 = $('#' + pdfFileProp).attr('checked') ? true :
            false;
        if ( !propValue1 && !propValue2) {
            $('#' + appName + 'ErrorDialog').dialog('open');
            propValue1 = true;
            $(this).attr('checked', true);
        }
        if (!propValue1) {
            $('#' + maxAdminTxtSizeProp).attr('disabled', true);
        } else {
            $('#' + maxAdminTxtSizeProp).removeAttr('disabled');
        }
        OC.AppConfig.setValue(appName, 'txtFile', propValue1);
    });

    /**
     * Enables/Disables the '<appName>MaxAdminTxtSize' text field on load
     * acording to the value of the <appName>TxtFile setting
     */
    var isTxtFilePropChecked = $('#' + txtFileProp).attr('checked') ? true :
        false;
    if (!isTxtFilePropChecked) {
        $('#' + maxAdminTxtSizeProp).attr('disabled', true);
    }

    /**
     * Catches the 'change' event when changing the status of the
     * <appName>PdfFile checkbox.
     *
     * @remarks: Note that if this setting is unchecked and the <appName>TxtFile
     *           setting is also unchecked, an error dialog will be shown and
     *           the checkbox will be checked again. This is needed to
     *           warranted that at least one option is shown on the login page:
     *           either a link to the txt file contents, a link to a pdf file,
     *           or both.
     */
    $('#' + pdfFileProp).on('change', function(){
        var propValue1 = $(this).attr('checked') ? true : false;
        var propValue2 = $('#' + txtFileProp).attr('checked') ? true :
            false;
         if ( !propValue1 && !propValue2 ) {
            $('#' + appName + 'ErrorDialog').dialog('open');
            propValue1 = true;
            $(this).attr('checked', true);
        }
        OC.AppConfig.setValue(appName, 'pdfFile', propValue1);
    });

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
     * Catches the 'change' event of the '<appName>MaxAdminTxtSize' input in
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
     * Only allows digits for the '<appName>MaxAdminFileSize' input
     *
     * @remarks: This code was taken from (some modifications were done):
     * - How to allow only numeric (0-9) in HTML inputbox using jQuery?
     *   Answer by SpYk3HH
     *   http://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
     */
    $('#' + maxAdminTxtSizeProp).keydown(function (e) {
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
    });

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
    $('#' + appName + 'ErrorDialog').dialog({
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
