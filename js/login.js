/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Script to setup the html elements of the login template
 */
$(document).ready(function(){
    'use strict';

    var appId = 'agreedisclaimer';
    var showTxt;
    var txtFileProp = appId + 'TxtFile';
    var txtContents;
    var showPdf;
    var pdfFileProp = appId + 'PdfFile';
    var pdfLink;
    var pdfPath;
    var pdfIcon;
    var errorPdf;

    /**
     * Ajax request for calling the settings#get_settings route
     */
    var baseUrl = OC.generateUrl('/apps/' + appId + '/settings/get_all');
    $.ajax({
        type: 'GET',
        url: baseUrl,
        async: false,
        success: function(settings) {
            var adminSettings = settings['adminSettings'];
            showTxt = adminSettings[txtFileProp]['value'];
            showPdf = adminSettings[pdfFileProp]['value'];

            if (showTxt === 'true') {
                if (adminSettings[txtFileProp]['file']['error'] === '') {
                    //If there weren't any error, the file contents will be
                    //shown
                    txtContents = adminSettings[txtFileProp]['file']['content'];
                } else {
                    //Otherwise an error will be displayed
                    txtContents = adminSettings[txtFileProp]['file']['error'];
                }
            }

            if (showPdf === 'true') {
                pdfIcon = settings['pdfIcon'];
                errorPdf = false;
                if (adminSettings[pdfFileProp]['file']['error'] === '') {
                    //If there weren't any error, a link to the pdf will be
                    //shown
                    pdfPath = adminSettings[pdfFileProp]['file']['url'];
                } else {
                    //Otherwise an error will be displayed
                    pdfPath = adminSettings[pdfFileProp]['file']['error'];
                    errorPdf = true;
                }
            }
        }
    });

    /**
     * Injects the disclaimer div in the login form. I wish I could do this on
     * the login template, but it seems that it isn't possible. I didn't got any
     * answer to my question on the developer list:
     * - Returning Template for login page
     *   https://mailman.owncloud.org/pipermail/devel/2015-July/001446.html
     */
    function injectDisclaimer() {
        //Removes the rounded borders of the password field by changing its
        //class from "groupbottom" to "groupmiddle"
        $('#password').parent().removeClass("groupbottom");
        $('#password').parent().addClass("groupmiddle");

        var text = t(appId, 'I have read and understood the ' +
            '%s1disclaimer of liability%s2');
        var keywords;
        if (showTxt === 'true') {
            //If the link to the txt file is supposed to be shown, the
            //placeholders: '%s1' and '%s2' will be used to render an html
            //anchor (<a> html tag)
            keywords = {
                '%s1': '<a id="' + appId + 'Link">',
                '%s2': '</a>'
            }
        } else {
            //Otherwise, they will be ignored
            keywords = {
                '%s1': '',
                '%s2': ''
            }
        }
        text = multiple_replace(text, keywords); 

        pdfLink = '';
        if (showPdf === 'true') {
            pdfLink = '    <a id="' + appId + 'PdfLink" ';
            if (!errorPdf) {
                //If the pdf file exist, then a link to it will be rendered;
                //otherwise, an error will be shown when clicking on it (see
                //the definition of the  "$('#' + appId + 'PdfLink').click"
                //event
                pdfLink = pdfLink + 'href="' + pdfPath + '"';
            }
            //The link is closed and the pdf icon is attached
            pdfLink = pdfLink + '>\n' +
                                '      <img src="' + pdfIcon + '"/>\n' +
                                '    </a>\n'; 
        }
        
        //Now we join everything together and add it after the parent of the
        //password field
        var disclaimerHtml = '<div class="' + appId + '">\n' +
                             '    <input id="' + appId + 'Checkbox" ' +
                                        'name="' + appId + 'Checkbox" ' +
                                        'type="checkbox"/>\n' +
                             '    <div id="' + appId + 'Div">\n' +
                             '        ' + text + '\n' +
                             '    </div>\n' + 
                                  pdfLink +
                             '</div>';
        $('#password').parent().after(disclaimerHtml);
    }
    injectDisclaimer();

    if (showPdf === 'false') {
        //If the pdf link isn't going to be displayed, then the width of for the
        //disclaimer notice will be increased. The space for the pdf icon will
        //be available since this image won't be shown
        $('#' + appId + 'Div').width('215px');
    }

    /**
     * Injects an error dialog in case that the PDF file doesn't exist. It will
     * be inserted at the end of the body tag
     *
     * @param   string  pdfError    Text of the error message when a pdf link
     *          couldn't be rendered properly
     */
    function injectErrorPdfDialog(pdfError){
        var dialogHtml;
        var title = t(appId, 'File not found');

        dialogHtml = '<div id="' + appId + 'ErrorDialog"\n' + 
                     '     title="' + title + '">\n' +
                     '    <p>\n' +
                     '        ' + pdfError + '\n' +
                     '    </p>\n' +
                     '</div>';
        $('body').append(dialogHtml);
    }

    if (errorPdf) {
        //If the pdf file doesn't exist or there is a permissions error, then
        //the error dialog will be injected
        injectErrorPdfDialog(pdfPath);
        $('#' + appId + 'ErrorDialog').dialog({
            autoOpen: false,
            width: 'auto',
            resizable: false,
            modal: true,
            closeText: 'Close',
            buttons: [
                {
                    text: t(appId, 'Ok'),
                    click: function() {
                        $(this).dialog('close');
                    }
                },
            ]
        });

        //The pdf link will open the error dialog
        $('#' + appId + 'PdfLink').click(function(e) {
            $('#' + appId + 'ErrorDialog').dialog('open');
            e.preventDefault;
        });
    }

    /**
     * Injects a dialog with the disclaimer's text
     *
     * @param   string  disclaimerText  Contents of the txt file
     */
    function injectDisclaimerDialog(disclaimerText) {
        var dialogHtml;
        var title = t(appId, 'Disclaimer of liability');

        dialogHtml = '<div id="' + appId + 'Dialog"\n' + 
                     '     title="' + title + '">\n' +
                     '    <p>\n' +
                     '        ' + disclaimerText + '\n' +
                     '    </p>\n' +
                     '</div>';
        $('body').append(dialogHtml);
    }
    
    if (showTxt === 'true') {
        //If the txt link is active, then the dialog with the disclaimer's text
        //will be injected
        injectDisclaimerDialog(txtContents);
        $('#' + appId + 'Dialog').dialog({
            autoOpen: false,
            width: 550,
            resizable: false,
            modal: true,
            closeText: 'Close',
            buttons: [
                {
                    text: t(appId, 'Agree'),
                    click: function() {
                        $('#' + appId + 'Checkbox').attr('checked', true);
                        $(this).dialog('close');
                    }
                },
                {
                    text: t(appId, 'Decline'),
                    click: function() {
                        $('#' + appId + 'Checkbox').removeAttr('checked');
                        $(this).dialog('close');
                    }
                }
            ]
        });

        /**
         * Shows the disclaimer's text when cliking on the link:
         * <appId>Link
         */
        $('#' + appId + 'Link').click(function(e) {
            $('#' + appId + 'Dialog').dialog('open');
            e.preventDefault;
        });
    }
});
