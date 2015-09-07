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
 * Class for injecting the disclaimer menu entry
 */
(function(window, $, exports, undefined) {
    'use strict';

    /**
     * Creates an UserPage object
     *
     * @param string appName    Application's name
     * @param string userLang   Current user language
     */
    var UserPage = function(appName, userLang) {
        this.appName = appName;
        this.userLang = userLang;
        this.disclaimerTitle = '';
    };

    /**
     * Initializes the UserPage object
     */
    UserPage.prototype.init = function() {
        //Loads the txt file contents, the pdf link, and the disclaimer layout
        //by calling the settings#get_settings route through an ajax request
        var baseUrl = OC.generateUrl('/apps/' + this.appName +
                '/settings/get_disclaimer_layout');

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
                obj.disclaimerLayout = settings['layout']; 
                if (obj.disclaimerLayout !== '') {
                    obj.showTxt = settings['txtFileData']['value'];
                    obj.showPdf = settings['pdfFileData']['value'];
                    obj.disclaimerTitle = t(obj.appName,
                        settings['textData'][1]['name']);
                    obj.disclaimerMenu = t(obj.appName,
                        settings['textData'][1]['menu']);

                    if (obj.showTxt) {
                        if (settings['txtFileData']['error'] === '') {
                            //If there weren't any error, the file contents will
                            //be shown
                            obj.txtContents =
                                settings['txtFileData']['contents'];
                        } else {
                            //Otherwise an error will be displayed
                            obj.txtContents = settings['txtFileData']['error'];
                        }
                    }

                    if (obj.showPdf) {
                        obj.pdfIcon = settings['pdfFileData']['icon'];
                        obj.errorPdf = false;
                        if (settings['pdfFileData']['error'] === '') {
                            //If there weren't any error, a link to the pdf will
                            //be shown
                            obj.pdfPath = settings['pdfFileData']['url'];
                        } else {
                            //Otherwise an error will be displayed
                            obj.pdfPath = settings['pdfFileData']['error'];
                            obj.errorPdf = true;
                        }
                    }
                }
            }
        });
    };

    /**
     * Injects a dialog with the disclaimer's text
     */
    UserPage.prototype.injectDisclaimerDialog = function() {
        var dialogDiv = $('<div />');
        dialogDiv.attr('id', this.appName + 'Dialog');
        dialogDiv.attr('title', this.disclaimerTitle);
        var disclaimerTextTag = $('<p />');
        disclaimerTextTag.html(this.txtContents);
        dialogDiv.append(disclaimerTextTag);
        $('body').append(dialogDiv);

        //Quick hack to be able to access the 'this' object
        //properties inside the jquery event handlers.
        var obj = this;

        $('#' + this.appName + 'Dialog').dialog({
            autoOpen: false,
            width: 550,
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
    };
    /**
     * Injects an error dialog in case that the PDF file doesn't exist. It will
     * be inserted at the end of the body tag
     */
    UserPage.prototype.injectErrorPdfDialog = function(){
        var dialogDiv = $('<div />');
        dialogDiv.attr('id', this.appName + 'ErrorDialog');
        dialogDiv.attr('title', t(this.appName, 'File not found'));
        var disclaimerTextTag = $('<p />');
        disclaimerTextTag.html(this.pdfError);
        dialogDiv.append(disclaimerTextTag);
        $('body').append(dialogDiv);

        //Quick hack to be able to access the 'this' object
        //properties inside the jquery event handlers.
        var obj = this;

        $('#' + this.appName + 'ErrorDialog').dialog({
            autoOpen: false,
            width: 550,
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
    };

    /**
     * Injects the menu entry into the user's area menu
     */
    UserPage.prototype.injectDisclaimerMenuEntry = function() {
        if (this.disclaimerLayout !== '') {
            var pdfLink;
            var menuEntry = $('<span />');

            menuEntry.addClass(this.appName + '-span').addClass(
                this.appName + '-' + this.disclaimerLayout);

            var disclaimerLink = $('<a />');
            disclaimerLink.text(this.disclaimerMenu);
            disclaimerLink.attr('title', this.disclaimerTitle);
            menuEntry.append(disclaimerLink);

            //Quick hack to be able to access the 'this' object properties
            //inside the jquery event handlers.
            var obj = this;
            if (this.showTxt) {
                this.injectDisclaimerDialog();
                disclaimerLink.click(function(e) {
                    $('#' + obj.appName + 'Dialog').dialog('open');
                    e.preventDefault;
                });
                if (this.showPdf) {
                    pdfLink = $('<a />');
                    pdfLink.attr('title', this.disclaimerTitle);
                }
            } else {
                pdfLink = disclaimerLink;
            }

            if (this.showPdf) {
                if (!this.errorPdf) {
                    if (this.showTxt) {
                        var pdfIconTag = $('<img />');
                        pdfIconTag.attr('src', this.pdfIcon);
                        pdfLink.append(pdfIconTag);
                        disclaimerLink.after(pdfLink);
                    }
                    pdfLink.attr('href', this.pdfPath);
                    pdfLink.attr('target', '_blank');
                } else {
                    this.injectErrorPdfDialog();
                    pdfLink.click(function(e) {
                        $('#' + obj.appName + 'ErrorDialog').dialog('open');
                        e.preventDefault;
                    });
                }
            }

            if (this.disclaimerLayout == 'top-right') {
                $('#header form.searchbox').after(menuEntry);
            } else { // disclaimerLayout == 'top-left'
                $('#header a.menutoggle').after(menuEntry);
            }
        }
    };

    exports.UserPage = UserPage;
})(window, jQuery, AgreeDisclaimer);

$(document).ready(function() {
    'use strict';
    /** Fix it: it would be nice to adquire it from somewhere else */
    var appName = 'agreedisclaimer';
    var userLang = OC.getLocale();
    var userPage = new AgreeDisclaimer.UserPage(appName, userLang);

    userPage.init();
    userPage.injectDisclaimerMenuEntry();
});
