<?php
/**
 * ownCloud - agreedisclaimer
 *
 * This file is licensed under the MIT License. See the COPYING file.
 *
 * @author Josef Meile <technosoftgratis@okidoki.com.co>
 * @copyright Josef Meile 2015
 */

/**
 * Template that will be rendered on the login page
 */

$appId = $_['appId'];

/**
 * Adds the javascript utilities to the login page
 */
script($appId, 'utils');

/**
 * Adds the javascript to the login page
 */
script($appId, 'login');

/**
 * Adds the style sheets file to the login page
 */
style($appId, 'login');
?>

<!-- Every html code after this gets ignored :-(
     That's why I have to code everything on javascript. I asked already about
     this on the developers maillist, but I didn't get any answer:
     * Returning Template for login page
       https://mailman.owncloud.org/pipermail/devel/2015-July/001446.html
-->
