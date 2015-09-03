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
 * Class to setup the jquery datepicker widgets
 */
(function(window, $, exports, undefined) {
    'use strict';

    /**
     * Creates a DatepickerUtils object
     *
     * @param string                appName
     *            Application's name
     * @param string                userLang
     *            Current user language
     * @param array                 monthNames
     *            Array with the translated month names
     * @param array                 dayNames
     *            Array with the translated day names
     * @param string                datepickerAppFormat
     *            Datepicker date format used by the app to store dates
     * @param AgreeDisclaimer.Utils Utils
     *            Utils object to access several utility functions
     */
    var DatepickerUtils = function(appName, userLang, monthNames, dayNames,
                              datepickerAppFormat, Utils) {
        this.appName = appName;
        this.userLang = userLang;
        this.monthNames = monthNames;
        this.dayNames = dayNames;
        this.utils = Utils;

        //Do not change this. It will be got from the l10n file
        this.datepickerUserFormat = t(this.appName, 'mm/dd/yy');
        this.datepickerAppFormat = datepickerAppFormat;
    };

    /**
      * Fix it: This is an ugly hack, but since OwnCloud didn't include the
      * jquery datepicker locales, I had to inject my own. I know that there are
      * some applications (calendar and tasks)  that have this settings, but I
      * don't want to depend on them. Better would be to have this on OwnCloud's
      * core (just include the jquery ui datepicker locales)
      *
      * If you want to translate your own language, see the
      * jquery.ui.datepicker-* files inside a jquery distribution. You will have
      * to extract the strings from there and paste them into the respective
      * l10n file
      */
    DatepickerUtils.prototype.initDatePickerLocale = function() {
        //Quick hack to be able to access the 'this' object
        //properties
        //inside the jquery event handlers.
        var obj = this;

        $.datepicker.regional[this.userLang] = {
            closeText: t(obj.appName, 'Done'),
            prevText: '&laquo;' + t(obj.appName, 'Prev'),
            nextText: t(obj.appName, 'Next') + '&raquo;',
            currentText: t(obj.appName, 'Today'),
        
            //The OwnCloude core already has this
            monthNames: obj.monthNames, 
            //Taken from: owncloud/core/js/share.js
            monthNamesShort: $.map(obj.monthNames, function(v) {
                                 return v.slice(0,3)+'.';
                             }
            ),
            //The OwnCloude core already has this
            dayNames: obj.dayNames, 
            //Taken from: owncloud/core/js/share.js
            dayNamesShort: $.map(obj.dayNames, function(v) {
                               return v.slice(0,3); 
                           }
            ),
            //Taken from: owncloud/core/js/share.js
            dayNamesMin: $.map(obj.dayNames, function(v) {
                             return v.slice(0,2);
                         }
            ), 
            weekHeader: t(obj.appName, 'Wk'),
   
            dateFormat: obj.datepickerUserFormat, 
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
    };

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
     */
    DatepickerUtils.prototype.configDatepicker = function(id) {
        var propValue = $('#' + id).val();

        //Quick hack to be able to access the 'this' object
        //properties
        //inside the jquery event handlers.
        var obj = this;

        $('#' + id).datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            minDate: new Date(2015, 0, 1),
            maxDate: new Date(),
            onClose: function(selectedDate) {
                var propValue = $(this).val();
                var propName = $(this).attr('id').split(obj.appName)[1];
                //Do not change the date format here. It is the one using to
                //storing dates
                var convertedDate = obj.utils.convertDate(propValue,
                        obj.datepickerUserFormat, obj.datepickerAppFormat);
                OC.AppConfig.setValue(obj.appName, propName, convertedDate);
            }
        });

        //Changes the jquery datepicker locale
        $('#' + id).datepicker('option',
            $.datepicker.regional[obj.userLang]
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
                var propName = $(this).attr('id').split(obj.appName)[1];
                $(this).val('');
                OC.AppConfig.setValue(obj.appName, propName, '');
            }
            e.preventDefault();
            return;
        });
        $('#' + id).val(propValue);
    };

    exports.DatepickerUtils = DatepickerUtils;
})(window, jQuery, AgreeDisclaimer);
