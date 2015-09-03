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

var AgreeDisclaimer = AgreeDisclaimer || {};

/**
 * Class to render the disclaimer on the login page
 */
(function(window, $, exports, undefined) {
    'use strict';

    /**
     * Creates a LoginPage object
     *
     * @param string                appName
     *            Application's name
     * @param AgreeDisclaimer.Utils Utils
     *            Utils object to access several utility functions

     */
    var LoginPage = function(appName, Utils) {
        this.appName = appName;
        this.disclaimerAccepted = false;
        this.disclaimerText = '';
        this.disclaimerTitle = '';
        this.utils = Utils;
    };

    /**
     * Ajax request for calling the settings#get_settings route
     */
    LoginPage.prototype.init = function() {
        var baseUrl = OC.generateUrl('/apps/' + this.appName +
                          '/settings/get_settings');

        //Quick hack to be able to access the 'this' object properties. You can
        //also achieve this by setting the context setting from ajax to 'this',
        //then you can just write "this.prop"; however, if you want to access
        //the ajax object itself, you won't be able to because 'this' won't
        //reffer to it anymore
        var obj = this;

        $.ajax({
            url: baseUrl,
            type: 'GET',
            async: false,
            contentType: 'application/json; charset=utf-8',
            success: function(settings) {
                obj.showTxt = settings['txtFileData']['value'];
                obj.showPdf = settings['pdfFileData']['value'];
                obj.useCookie = settings['cookieData']['value']; 
                obj.disclaimerText = t(obj.appName,
                    settings['textData']['text']);
                obj.disclaimerTitle = t(obj.appName,
                    settings['textData']['name']);
                if (obj.showTxt) {
                    if (settings['txtFileData']['error'] === '') {
                        //If there weren't any error, the file contents will be
                        //shown
                        obj.txtContents = settings['txtFileData']['contents'];
                    } else {
                        //Otherwise an error will be displayed
                        obj.txtContents = settings['txtFileData']['error'];
                    }
                }

                if (obj.showPdf) {
                    obj.pdfIcon = settings['pdfFileData']['icon'];
                    obj.errorPdf = false;
                    if (settings['pdfFileData']['error'] === '') {
                        //If there weren't any error, a link to the pdf will be
                        //shown
                        obj.pdfPath = settings['pdfFileData']['url'];
                    } else {
                        //Otherwise an error will be displayed
                        obj.pdfPath = settings['pdfFileData']['error']; 
                        obj.errorPdf = true;
                    }
                }

                if (obj.useCookie) {
                    obj.disclaimerAccepted =
                        settings['cookieData']['checkedCookie'];
                }
            }
        });
    };

    /**
     * Injects the disclaimer div in the login form. I wish I could do this on
     * the login template, but it seems that it isn't possible. I didn't got any
     * answer to my question on the developer list:
     * - Returning Template for login page
     *   https://mailman.owncloud.org/pipermail/devel/2015-July/001446.html
     */
    LoginPage.prototype.injectDisclaimer = function() {
        //Removes the rounded borders of the password field by changing its
        //class from "groupbottom" to "groupmiddle"
        $('#password').parent().removeClass("groupbottom");
        $('#password').parent().addClass("groupmiddle");

        var keywords;
        if (this.showTxt) {
            //If the link to the txt file is supposed to be shown, the
            //placeholders: '%s1' and '%s2' will be used to render an html
            //anchor (<a> html tag)
            keywords = {
                '%s1': '<a id="' + this.appName + 'Link">',
                '%s2': '</a>'
            }
        } else {
            //Otherwise, they will be ignored
            keywords = {
                '%s1': '',
                '%s2': ''
            }
        }
        this.disclaimerText = this.utils.multiple_replace(this.disclaimerText,
                                  keywords); 
        
        var disclaimerDiv = $('<div />');
        disclaimerDiv.attr('class', this.appName);
        var checkbox = $('<input />');
        checkbox.attr('id', this.appName + 'Checkbox');
        checkbox.attr('name', this.appName + 'Checkbox');
        checkbox.attr('type', 'checkbox');
        checkbox.prop('checked', this.disclaimerAccepted);
        disclaimerDiv.append(checkbox);
        
        var textDiv = $('<div />');
        textDiv.attr('id', this.appName + 'Div');
        textDiv.html(this.disclaimerText);
        disclaimerDiv.append(textDiv);

        if (this.showPdf) {
            var pdfLink;
            pdfLink = $('<a />');
            pdfLink.attr('id', this.appName + 'PdfLink');
            if (!this.errorPdf) {
                //If the pdf file exist, then a link to it will be rendered;
                //otherwise, an error will be shown when clicking on it (see
                //the definition of the  "$('#' + appName + 'PdfLink').click"
                //event
                pdfLink.attr('target', '_blank');
                pdfLink.attr('href', this.pdfPath);
            }
            var pdfIconTag = $('<img />');
            pdfIconTag.attr('src', this.pdfIcon);
            pdfLink.append(pdfIconTag);
            disclaimerDiv.append(pdfLink);
        }
        $('#password').parent().after(disclaimerDiv);
    };

    /**
     * Injects an error dialog in case that the PDF file doesn't exist. It will
     * be inserted at the end of the body tag
     */
    LoginPage.prototype.injectErrorPdfDialog = function() {
        if (this.errorPdf) {
            //If the pdf file doesn't exist or there is a permissions error, then
            //the error dialog will be injected
            var dialogDiv = $('<div />');
            dialogDiv.attr('id', this.appName + 'ErrorDialog');
            dialogDiv.attr('title', t(this.appName, 'File not found'));
            var dialogText = $('<p />');
            dialogText.html(this.pdfPath);
            dialogDiv.append(dialogText);
            $('body').append(dialogDiv);

            //Quick hack to be able to access the 'this' object properties
            //inside the jquery event handlers. 
            var obj = this;
            $('#' + this.appName + 'ErrorDialog').dialog({
                autoOpen: false,
                width: 'auto',
                resizable: false,
                modal: true,
                closeText: t(obj.appName, 'Close'),
                buttons: [
                    {
                        text: t(obj.appName, 'Ok'),
                        click: function() {
                            $(this).dialog('close');
                        }
                    },
                ]
            });

            //The pdf link will open the error dialog
            $('#' + this.appName + 'PdfLink').click(function(e) {
                $('#' + obj.appName + 'ErrorDialog').dialog('open');
                e.preventDefault;
            });
        }
    };

    /**
     * Injects a dialog with the disclaimer's text
     */
    LoginPage.prototype.injectDisclaimerDialog = function() {
        if (this.showTxt) {
            //If the txt link is active, then the dialog with the disclaimer's
            //text will be injected

            var dialogDiv = $('<div />');
            dialogDiv.attr('id', this.appName + 'Dialog');
            dialogDiv.attr('title', this.disclaimerTitle);
            var disclaimerText = $('<p />');
            disclaimerText.html(this.txtContents);
            dialogDiv.append(disclaimerText);
            $('body').append(dialogDiv);

            //Quick hack to be able to access the 'this' object properties
            //inside the jquery event handlers. 
            var obj = this;
            $('#' + this.appName + 'Dialog').dialog({
                autoOpen: false,
                width: 550,
                resizable: false,
                modal: true,
                closeText: t(obj.appName, 'Close'),
                buttons: [
                    {
                        text: t(obj.appName, 'Agree'),
                        click: function() {
                            $('#' + obj.appName + 'Checkbox').attr('checked',
                                true);
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: t(obj.appName, 'Decline'),
                        click: function() {
                            $('#' + obj.appName + 'Checkbox').removeAttr(
                                'checked');
                            $(this).dialog('close');
                        }
                    }
                ]
            });

            /**
             * Shows the disclaimer's text when cliking on the link:
             * <appName>Link
             */
            $('#' + this.appName + 'Link').click(function(e) {
                $('#' + obj.appName + 'Dialog').dialog('open');
                e.preventDefault;
            });
        }
    };
    exports.LoginPage = LoginPage;
})(window, jQuery, AgreeDisclaimer);

$(document).ready(function() {
    'use strict';
    /** Fix it: it would be nice to adquire it from somewhere else */
    var appName = 'agreedisclaimer';

    var utils = new AgreeDisclaimer.Utils();
    var loginPage = new AgreeDisclaimer.LoginPage(appName, utils);
    loginPage.init();
    loginPage.injectDisclaimer();
    if (!loginPage.showPdf) {
        //If the pdf link isn't going to be displayed, then the width of for the
        //disclaimer notice will be increased. The space for the pdf icon will
        //be available since this image won't be shown
        $('#' + appName + 'Div').width('215px');
    }
    loginPage.injectErrorPdfDialog();
    loginPage.injectDisclaimerDialog();
});
