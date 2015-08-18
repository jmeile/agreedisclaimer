/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Script to setup the html elements of the login template
 */
$(document).ready(function(){
    'use strict';
    /** Fix it: it would be nice to adquire it from somewhere else */
    var appName = 'agreedisclaimer';

    //Application settings
    var showTxt;
    var txtFileProp = appName + 'TxtFile';
    var txtContents;
    var showPdf;
    var pdfFileProp = appName + 'PdfFile';
    var pdfLink;
    var pdfPath;
    var pdfIcon;
    var errorPdf;
    var useCookie;
    var disclaimerAccepted = false;
    var disclaimerText = '';
    var disclaimerTitle = '';

    /**
     * Ajax request for calling the settings#get_settings route
     */
    var baseUrl = OC.generateUrl('/apps/' + appName + '/settings/get_settings');
    $.ajax({
        url: baseUrl,
        type: 'GET',
        async: false,
        contentType: 'application/json; charset=utf-8',
        success: function(settings) {
            showTxt = settings['txtFileData']['value'];
            showPdf = settings['pdfFileData']['value'];
            useCookie = settings['cookieData']['value']; 
            disclaimerText = t(appName, settings['textData']['text']);
            disclaimerTitle = t(appName, settings['textData']['name']);

            if (showTxt) {
                if (settings['txtFileData']['error'] === '') {
                    //If there weren't any error, the file contents will be
                    //shown
                    txtContents = settings['txtFileData']['contents'];
                } else {
                    //Otherwise an error will be displayed
                    txtContents = settings['txtFileData']['error'];
                }
            }

            if (showPdf) {
                pdfIcon = settings['pdfFileData']['icon'];
                errorPdf = false;
                if (settings['pdfFileData']['error'] === '') {
                    //If there weren't any error, a link to the pdf will be
                    //shown
                    pdfPath = settings['pdfFileData']['url'];
                } else {
                    //Otherwise an error will be displayed
                    pdfPath = settings['pdfFileData']['error']; 
                    errorPdf = true;
                }
            }

            if (useCookie) {
                disclaimerAccepted = settings['cookieData']['checkedCookie'];
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

        var keywords;
        if (showTxt) {
            //If the link to the txt file is supposed to be shown, the
            //placeholders: '%s1' and '%s2' will be used to render an html
            //anchor (<a> html tag)
            keywords = {
                '%s1': '<a id="' + appName + 'Link">',
                '%s2': '</a>'
            }
        } else {
            //Otherwise, they will be ignored
            keywords = {
                '%s1': '',
                '%s2': ''
            }
        }
        disclaimerText = multiple_replace(disclaimerText, keywords); 

        pdfLink = '';
        if (showPdf) {
            pdfLink = '    <a id="' + appName + 'PdfLink" ';
            if (!errorPdf) {
                //If the pdf file exist, then a link to it will be rendered;
                //otherwise, an error will be shown when clicking on it (see
                //the definition of the  "$('#' + appName + 'PdfLink').click"
                //event
                pdfLink = pdfLink + 'target="_blank" href="' + pdfPath + '"';
            }
            //The link is closed and the pdf icon is attached
            pdfLink = pdfLink + '>\n' +
                                '      <img src="' + pdfIcon + '"/>\n' +
                                '    </a>\n'; 
        }
        
        //Now we join everything together and add it after the parent of the
        //password field
        var disclaimerHtml = '<div class="' + appName + '">\n' +
                             '    <input id="' + appName + 'Checkbox" ' +
                                        'name="' + appName + 'Checkbox" ' +
                                        'type="checkbox"'
        if (disclaimerAccepted) {
            disclaimerHtml = disclaimerHtml + ' checked';
        }
        disclaimerHtml = disclaimerHtml + '/>\n' +
                             '    <div id="' + appName + 'Div">\n' +
                             '        ' + disclaimerText + '\n' +
                             '    </div>\n' + 
                                  pdfLink +
                             '</div>';
        $('#password').parent().after(disclaimerHtml);
    }
    injectDisclaimer();

    if (!showPdf) {
        //If the pdf link isn't going to be displayed, then the width of for the
        //disclaimer notice will be increased. The space for the pdf icon will
        //be available since this image won't be shown
        $('#' + appName + 'Div').width('215px');
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
        var title = t(appName, 'File not found');

        dialogHtml = '<div id="' + appName + 'ErrorDialog"\n' + 
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
        $('#' + appName + 'ErrorDialog').dialog({
            autoOpen: false,
            width: 'auto',
            resizable: false,
            modal: true,
            closeText: 'Close',
            buttons: [
                {
                    text: t(appName, 'Ok'),
                    click: function() {
                        $(this).dialog('close');
                    }
                },
            ]
        });

        //The pdf link will open the error dialog
        $('#' + appName + 'PdfLink').click(function(e) {
            $('#' + appName + 'ErrorDialog').dialog('open');
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
        var title = disclaimerTitle; 

        dialogHtml = '<div id="' + appName + 'Dialog"\n' + 
                     '     title="' + title + '">\n' +
                     '    <p>\n' +
                     '        ' + disclaimerText + '\n' +
                     '    </p>\n' +
                     '</div>';
        $('body').append(dialogHtml);
    }
    
    if (showTxt) {
        //If the txt link is active, then the dialog with the disclaimer's text
        //will be injected
        injectDisclaimerDialog(txtContents);
        $('#' + appName + 'Dialog').dialog({
            autoOpen: false,
            width: 550,
            resizable: false,
            modal: true,
            closeText: 'Close',
            buttons: [
                {
                    text: t(appName, 'Agree'),
                    click: function() {
                        $('#' + appName + 'Checkbox').attr('checked', true);
                        $(this).dialog('close');
                    }
                },
                {
                    text: t(appName, 'Decline'),
                    click: function() {
                        $('#' + appName + 'Checkbox').removeAttr('checked');
                        $(this).dialog('close');
                    }
                }
            ]
        });

        /**
         * Shows the disclaimer's text when cliking on the link:
         * <appName>Link
         */
        $('#' + appName + 'Link').click(function(e) {
            $('#' + appName + 'Dialog').dialog('open');
            e.preventDefault;
        });
    }
});
