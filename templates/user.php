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
 * Template that will be rendered on the user's area
 */

/**
 * Adds the javascript for locating the disclaimer 
 */
script($_['appName'], 'user');

/**
 * Adds the style sheets file to the disclaimer menu entry 
 */
style($_['appName'], 'user');
?>
