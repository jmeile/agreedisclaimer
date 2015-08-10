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
 * Routes for getting the app settings by using ajax 
 *
 * @return array    An array with the application routes
 */
return ['routes' => [
    /** Gets the info of the disclaimer files */
    ['name' => 'settings#get_files',
     'url' => '/settings/get_files', 'verb' => 'GET'],
]];
