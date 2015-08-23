/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Script for injecting the disclaimer menu entry 
 */
$(document).ready(function(){
    'use strict';
    /** Fix it: it would be nice to adquire it from somewhere else */
    var appName = 'agreedisclaimer';
    var userLang = OC.getLocale();
    var showTxt;
    var txtContents;
    var showPdf;
    var pdfLink;
    var pdfPath;
    var pdfIcon;
    var errorPdf;
    var disclaimerTitle = '';
    var disclaimerLayout;

    //Loads the txt file contents, the pdf link, and the disclaimer layout by
    //calling the settings#get_settings route through an ajax request
    var baseUrl = OC.generateUrl('/apps/' + appName +
            '/settings/get_disclaimer_layout');
    $.ajax({
        url: baseUrl,
        type: 'GET',
        async: false,
        contentType: 'application/json; charset=utf-8',
        success: function(settings) {
            disclaimerLayout = settings['layout']; 
            if (disclaimerLayout !== '') {
                showTxt = settings['txtFileData']['value'];
                showPdf = settings['pdfFileData']['value'];
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
            }
        }
    });


    /**
     * Injects a dialog with the disclaimer's text
     *
     * @param   string  disclaimerText  Contents of the txt file
     */
    function injectDisclaimerDialog(disclaimerText) {
        var dialogDiv = $('<div />');
        dialogDiv.attr('id', appName + 'Dialog');
        dialogDiv.attr('title', disclaimerTitle);
        var disclaimerTextTag = $('<p />');
        disclaimerTextTag.html(disclaimerText);
        dialogDiv.append(disclaimerTextTag);
        $('body').append(dialogDiv);

        $('#' + appName + 'Dialog').dialog({
            autoOpen: false,
            width: 550,
            resizable: false,
            modal: true,
            closeText: t(appName, 'Close'),
            buttons: [
                {
                    text: t(appName, 'Ok'),
                    click: function() {
                        $(this).dialog('close');
                    }
                },
            ]
        });
    }

    /**
     * Injects an error dialog in case that the PDF file doesn't exist. It will
     * be inserted at the end of the body tag
     *
     * @param   string  pdfError    Text of the error message when a pdf link
     *          couldn't be rendered properly
     */
    function injectErrorPdfDialog(pdfError){
        var dialogDiv = $('<div />');
        dialogDiv.attr('id', appName + 'ErrorDialog');
        dialogDiv.attr('title', t(appName, 'File not found'));
        var disclaimerTextTag = $('<p />');
        disclaimerTextTag.html(pdfError);
        dialogDiv.append(disclaimerTextTag);
        $('body').append(dialogDiv);

        $('#' + appName + 'ErrorDialog').dialog({
            autoOpen: false,
            width: 550,
            resizable: false,
            modal: true,
            closeText: t(appName, 'Close'),
            buttons: [
                {
                    text: t(appName, 'Ok'),
                    click: function() {
                        $(this).dialog('close');
                    }
                },
            ]
        });
    }

    /**
     * Injects the menu entry into the user's area menu
     */
    function injectDisclaimerMenuEntry() {
        var pdfLink;
        var menuEntry = $('<span />');
        menuEntry.addClass(appName + '-span').addClass(
            appName + '-' + disclaimerLayout);

        var disclaimerLink = $('<a />');
        disclaimerLink.html(disclaimerTitle);
        menuEntry.append(disclaimerLink);
        if (showTxt) {
            injectDisclaimerDialog(txtContents);
            disclaimerLink.click(function(e) {
                $('#' + appName + 'Dialog').dialog('open');
                e.preventDefault;
            });
            if (showPdf) {
                pdfLink = $('<a />');
            }
        } else {
            pdfLink = disclaimerLink;
        }

        if (showPdf) {
            if (!errorPdf) {
                if (showTxt) {
                    var pdfIconTag = $('<img />');
                    pdfIconTag.attr('src', pdfIcon);
                    pdfLink.append(pdfIconTag);
                    disclaimerLink.after(pdfLink);
                }
                pdfLink.attr('href', pdfPath);
                pdfLink.attr('target', '_blank');
            } else {
                injectErrorPdfDialog(pdfPath);
                pdfLink.click(function(e) {
                    $('#' + appName + 'ErrorDialog').dialog('open');
                    e.preventDefault;
                });
            }
        }

        if (disclaimerLayout == 'top-right') {
            $('#header form.searchbox').after(menuEntry);
        } else { // disclaimerLayout == 'top-left'
            $('#header a.menutoggle').after(menuEntry);
        }
    }
    if (disclaimerLayout !== '') {
        injectDisclaimerMenuEntry();
    }
});
