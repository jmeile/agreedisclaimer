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
 * Code taken from:
 * find multiple key words within a dictionary Javascript, answer by Rob W
 * http://stackoverflow.com/questions/8413651/find-multiple-key-words-within-a-dictionary-javascript
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
