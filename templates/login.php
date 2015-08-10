<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the LICENSE file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Template that will be rendered on the login page
 */

/**
 * Adds the javascript utilities to the login page
 */
script($_['appName'], 'utils');

/**
 * Adds the javascript to the login page
 */
script($_['appName'], 'login');

/**
 * Adds the style sheets file to the login page
 */
style($_['appName'], 'login');
?>

<!-- Fix it: Every html code after this gets ignored :-(
     That's why I have to code everything on javascript. I asked already about
     this on the developers maillist, but I didn't get any answer:
     * Returning Template for login page
       https://mailman.owncloud.org/pipermail/devel/2015-July/001446.html
-->
