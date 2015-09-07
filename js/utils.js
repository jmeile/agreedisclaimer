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
 * Utility functions to be used by other javascripts
 */
(function(window, $, exports, undefined) {
    'use strict';

    /**
     * Creates an Utils object
     */
    var Utils = function() {
    };

    /**
     * Does multiple string replacement in the entered string
     *
     * Code taken from:
     * find multiple key words within a dictionary Javascript, answer by Rob W
     * http://stackoverflow.com/questions/8413651/find-multiple-key-words-within-a-dictionary-javascript
     *
     * @param string str        String where the replacements are going to be
     *                          done
     * @param object keywors    Keywords and its replacements, ie:
     *                          {"blue": "red", "yellow": "orange} will replace
     *                          all the occurences from "blue" by "red" and from
     *                          "yellow" by "orange"
     *
     * @return string   The string with the replaced keywords
     */
    Utils.prototype.multipleReplace = function(str, keywords) { 
        var pattern = [];
        var key;
        var result;

        for (key in keywords) {
            // Sanitize the key, and push it in the list
            pattern.push(key.replace(/([[^$.|?*+(){}])/g, '\\$1'));
        }

        pattern = "(?:" + pattern.join(")|(?:") + ")"; //Create pattern
        pattern = new RegExp(pattern, "g");

        // Walk through the string, and replace every occurrence of the matched
        // tokens
        result = str.replace(pattern, function(full_match) {
                    return keywords[full_match];
                 });
        return result;
    };

    /**
     * Searchs all the keywords in the entered string and return all their
     * indexes
     *
     * This code was inspired on: 
     * - find multiple key words within a dictionary Javascript, answer by Rob W
     *   http://stackoverflow.com/questions/8413651/find-multiple-key-words-within-a-dictionary-javascript
     * - Javascript str.search() multiple instances, answer by nrabinowitz
     *   http://stackoverflow.com/questions/6825492/javascript-str-search-multiple-instances
     *
     * @param string str        String where the multiple search is going to be
     *                          executed
     * @param object keywors    Keywords array, ie: ["blue", "red"] 
     *
     * @return object   A dictionary with the keywords and the indexes where
     *                  they were found, ie:
     *                  {
     *                      "red": [0, 10],
     *                      "blue: [4, 14, 22]
     *                  }
     *                  This means that "red" was found on the entered string on
     *                  the indexes 0 and 10, while "blue" was found on the
     *                  indexes 4, 14, and 22.
     */
    Utils.prototype.multipleSearch = function(str, keywords) {
        var pattern = [];
        var key;
        var indexes = {};
        var index;
        var match;

        for (index in keywords) {
            key = keywords[index];
            indexes[key] = [];

            // Sanitize the key, and push it in the list
            pattern.push(key.replace(/([[^$.|?*+(){}])/g, '\\$1'));
        }

        pattern = "(?:" + pattern.join(")|(?:") + ")"; //Create pattern
        pattern = new RegExp(pattern, "g");

        while (match = pattern.exec(str)) {
            indexes[match[0]].push(match.index);
        }
        return indexes;
    }

    /**
     * Checks if the entered string is a valid date of the format dd/mm/yyyy
     *
     * @param string dateStr    Date string to validate
     */
    Utils.prototype.isValidDate = function(dateStr) {
        var dateRegex = /^\d{1,2}\/\d{1,2}\/\d{4}$/;

        //First we check if the format matches: dd/mm/yyyy
        var isValid = dateStr.match(dateRegex);

        if (isValid) {
            //Then we see if the date is valid, ie: the month is a number
            //between 1 and 12, and not 24 for example. For this, a new date
            //object is created, then the fields are compared with the original
            //date
            var dateParts = dateStr.split('/');
            var dateObj = new Date(dateStr[2], dateStr[1], dateStr[0]);
            if ((dateObj.getDate()      !== parseInt(dateStr[0])) ||
                (dateObj.getMonth() + 1 !== parseInt(dateStr[1])) ||
                (dateObj.getFullYear()  !== parseInt(dateStr[2]))) {
                isValid = false;
            }
        }
        return isValid;
    };

    /**
     * Converts the entered date to the specified date format
     *
     * @param string srcDate          Source date / date to convert
     * @param string srcFormat        Format of the source date
     * @param string destFormat       Format of the destination date
     *
     * @return string    The converted date
     *
     * @remarks    The date formats should be a 8 chars string with the
     *             following components:
     *             * 'dd': Two digit day of the month with or without leading
     *                zeroes
     *             * 'mm': Two digit month with or without leading zeroes
     *             * 'yy': Four digit year
     *             * The date separator is one character that should be repeated
     *               twice in the format string.
     *
     *             Some valid formats are:
     *             * 'dd.mm.yy' (separator = '.')
     *             * 'mm/dd/yy' (separator = '/')
     *
     *             The source date must be correctly formatted in the source
     *             format
     *
     *             Please note that I didn't choose 'yyyy' for the year since
     *             the jquery datepicker widget works with 'yy'; however, I
     *             really thing this shouldn't be like this :-(
     */
    Utils.prototype.convertDate = function(srcDate, srcFormat, destFormat) {
        if ((srcDate === '') || (srcFormat === destFormat)) {
            return srcDate;
        }
        var srcSeparator = srcFormat.substr(2,1);
        var destSeparator = destFormat.substr(2,1);

        var srcDateParts = srcDate.split(srcSeparator);
        var srcFormatParts = srcFormat.split(srcSeparator);
        var srcFormatPartsDict = {};
    
        //First we figure out the positions of each date component
        srcFormatParts.forEach(function(entry, index) {
            srcFormatPartsDict[entry] = index;
        });

        //Then we can assembly our date in the destination format
        var destFormatParts = destFormat.split(destSeparator);
        var destDateArray = [];
        destFormatParts.forEach(function(entry) {
            destDateArray.push(srcDateParts[srcFormatPartsDict[entry]]);
        });
        var destDate = destDateArray.join(destSeparator);
        return destDate;
    };


    /**
     * Only allows digits for text inputs
     *
     * @remarks: This code was taken from (some modifications were done):
     * - How to allow only numeric (0-9) in HTML inputbox using jQuery?
     *   Answer by SpYk3HH
     *   http://stackoverflow.com/questions/995183/how-to-allow-only-numeric-0-9-in-html-inputbox-using-jquery
     */
    Utils.prototype.onlyDigits = function(e) {
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
    };

    exports.Utils = Utils;
})(window, jQuery, AgreeDisclaimer);
