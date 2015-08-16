/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Utility functions to be used by other javascripts
 */

/**
 * Does multiple string replacement in the entered string
 *
 * Code taken from:
 * find multiple key words within a dictionary Javascript, answer by Rob W
 * http://stackoverflow.com/questions/8413651/find-multiple-key-words-within-a-dictionary-javascript
 *
 * @param string str    String where the replacements are going to be done
 * @param object keywors    Keywords and its replacements, ie:
 *                          {"blue": "red", "yellow": "orange} will replace all
 *                          the occurences from "blue" by "red" and from
 *                          "yellow" by "orange"
 */
function multiple_replace(str, keywords) {
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
}

/**
 * Checks if the entered string is a valid date of the format dd/mm/yyyy
 *
 * @param string dateStr    Date string to validate
 */
function isValidDate(dateStr) {
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
}

/**
 * Converts the entered date to the specified date format
 *
 * @param string srcDate          Source date / date to convert
 * @param string srcFormat        Format of the source date
 * @param string destFormat       Format of the destination date
 *
 * @return string    The converted date
 *
 * @remarks    The date formats should be a 8 chars string with the following
 *             components:
 *             * 'dd': Two digit day of the month with or without leading zeroes
 *             * 'mm': Two digit month with or without leading zeroes
 *             * 'yy': Four digit year
 *             * The date separator is one character that should be repeated
 *               twice in the format string.
 *
 *             Some valid formats are:
 *             * 'dd.mm.yy' (separator = '.')
 *             * 'mm/dd/yy' (separator = '/')
 *
 *             The source date must be correctly formatted in the source format
 *
 *             Please note that I didn't choose 'yyyy' for the year since the
 *             jquery datepicker widget works with 'yy'; however, I really thing
 *             this shouldn't be like this :-(
 */
function convertDate(srcDate, srcFormat, destFormat) {
    if ((srcDate === '') || (srcFormat === destFormat)) {
        return srcDate;
    }
    srcSeparator = srcFormat.substr(2,1);
    destSeparator = destFormat.substr(2,1);

    srcDateParts = srcDate.split(srcSeparator);
    srcFormatParts = srcFormat.split(srcSeparator);
    srcFormatPartsDict = {};
    
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
}
