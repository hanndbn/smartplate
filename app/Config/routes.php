<?php

/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
//	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
Router::connect('/', array('controller' => 'pages', 'action' => 'index'));
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
Router::connect(
        '/system', array('controller' => 'pages', 'action' => 'index', 'system' => true)
);

Router::connect(
        '/system/login', array('controller' => 'managements', 'action' => 'login', 'system' => true)
);
Router::connect(
        '/system/plate', array('controller' => 'tags', 'action' => 'index', 'system' => true)
);
Router::connect('/login', array('controller' => 'managements', 'action' => 'login'));
Router::connect('/logout', array('controller' => 'managements', 'action' => 'logout'));
Router::connect('/plate', array('controller' => 'tags', 'action' => 'index'));
Router::connect('/plate/label', array('controller' => 'tags', 'action' => 'label'));
Router::connect('/bookmark', array('controller' => 'bookmarks', 'action' => 'index'));
Router::connect('/bookmark/label', array('controller' => 'bookmarks', 'action' => 'label'));
Router::connect('/app', array('controller' => 'apps', 'action' => 'index'));
Router::connect('/app/label', array('controller' => 'apps', 'action' => 'label'));
Router::connect('/project', array('controller' => 'teams', 'action' => 'index'));
Router::connect('/account', array('controller' => 'accounts', 'action' => 'index'));
Router::connect('/policy', array('controller' => 'policy', 'action' => 'index'));
Router::connect('/access_history', array('controller' => 'accesshistory', 'action' => 'index'));

Router::connect('/rank/contents', array('controller' => 'accesslogs', 'action' => 'ContentsRanking'));
Router::connect('/rank/plate', array('controller' => 'accesslogs', 'action' => 'PlateRanking'));

Router::parseExtensions('php','csv','json');
/**
 * Adding rules for api
 */

Router::connect('/api/banner', array('controller' => 'banner', 'action' => 'index'));
Router::connect('/api/tails_contents', array('controller' => 'tailscontentsapi', 'action' => 'index'));
Router::connect('/api/tails_contents_test', array('controller' => 'tailscontentstestapi', 'action' => 'index'));
Router::connect('/convert_images', array('controller' => 'convertimages', 'action' => 'index'));
Router::connect('/convert_app_data', array('controller' => 'convertapi', 'action' => 'index'));

Router::connect('/api/test', array('controller' => 'testapi', 'action' => 'test'));
Router::connect('/api/login', array('controller' => 'loginapi', 'action' => 'index'));
Router::connect('/api/exclusive_login', array('controller' => 'exclusiveloginapi', 'action' => 'index'));
Router::connect('/api/save_datas', array('controller' => 'savedataapi', 'action' => 'index'));
Router::connect('/api/load_datas', array('controller' => 'Loaddataapi', 'action' => 'index'));
// user
Router::connect('/api/user/logout', array('controller' => 'logoutapi', 'action' => 'index'));
Router::connect('/api/user/registration', array('controller' => 'registrationapi', 'action' => 'index'));
Router::connect('/api/user/store_mail', array('controller' => 'storemailapi', 'action' => 'index'));
Router::connect('/api/user/change_password', array('controller' => 'changepasswordapi', 'action' => 'index'));
Router::connect('/api/user/reset_password', array('controller' => 'resetpasswordapi', 'action' => 'index'));
Router::connect('/api/user/info', array('controller' => 'userinfoapi', 'action' => 'index'));
//point
Router::connect('/api/point/add', array('controller' => 'pointaddapi', 'action' => 'index'));
Router::connect('/api/point/consume', array('controller' => 'pointuseapi', 'action' => 'index'));
//analytics
Router::connect('/api/analytics/top_count', array('controller' => 'topcountapi', 'action' => 'index'));
Router::connect('/api/analytics/top_plate_count_os', array('controller' => 'topplatecountosapi', 'action' => 'index'));
Router::connect('/api/analytics/top_contents_count', array('controller' => 'topcontentscountapi', 'action' => 'index'));
Router::connect('/api/analytics/top_contents_count_os', array('controller' => 'topcontentscountosapi', 'action' => 'index'));
Router::connect('/api/analytics/detail', array('controller' => 'detailapi', 'action' => 'index'));
Router::connect('/api/analytics/detail_contents', array('controller' => 'detailcontentsapi', 'action' => 'index'));
Router::connect('/api/analytics/detail_count', array('controller' => 'detailcountapi', 'action' => 'index'));
Router::connect('/api/analytics/detail_contents_count', array('controller' => 'detailcontentscountapi', 'action' => 'index'));
Router::connect('/api/analytics/hour', array('controller' => 'Hourapi', 'action' => 'index'));
Router::connect('/api/analytics/minute', array('controller' => 'Minuteapi', 'action' => 'index'));
Router::connect('/api/analytics/plate_all', array('controller' => 'Plateallapi', 'action' => 'index'));
Router::connect('/api/analytics/contents_all', array('controller' => 'Contentsallapi', 'action' => 'index'));
Router::connect('/api/analytics/unique_user', array('controller' => 'Uniqueuserapi', 'action' => 'index'));
Router::connect('/api/analytics/platform', array('controller' => 'Platformapi', 'action' => 'index'));

//tag
Router::connect('/api/tag/activate', array('controller' => 'activateapi', 'action' => 'index'));
Router::connect('/api/tag/activate_date', array('controller' => 'activatedateapi', 'action' => 'index'));
Router::connect('/api/tag/valid', array('controller' => 'validapi', 'action' => 'index'));
Router::connect('/api/tag/get', array('controller' => 'taggetapi', 'action' => 'index'));
Router::connect('/api/tag/put', array('controller' => 'tagputapi', 'action' => 'index'));
Router::connect('/api/tag/update', array('controller' => 'tagupdateapi', 'action' => 'index'));
Router::connect('/api/tag/tag_list', array('controller' => 'taglistapi', 'action' => 'index'));
Router::connect('/api/tag/links', array('controller' => 'linksapi', 'action' => 'index'));
//bookmark
Router::connect('/api/bookmark/content_list', array('controller' => 'contentlistapi', 'action' => 'index'));
Router::connect('/api/bookmark/content_detail', array('controller' => 'contentdetailapi', 'action' => 'index'));
Router::connect('/api/bookmark/put', array('controller' => 'contentputapi', 'action' => 'index'));
Router::connect('/api/bookmark/update', array('controller' => 'contentupdateapi', 'action' => 'index'));
Router::connect('/api/bookmark/save', array('controller' => 'contentsaveapi', 'action' => 'index'));

Router::connect('/api/bookmark/tiles_title_image', array('controller' => 'TilesTitleImage', 'action' => 'index'));
// Label
Router::connect('/api/label/add', array('controller' => 'labeladdapi', 'action' => 'index'));
Router::connect('/api/label/get', array('controller' => 'labelgetapi', 'action' => 'index'));

// custom
Router::connect('/api/analytics/custom', array('controller' => 'customdataapi', 'action' => 'index'));
Router::connect('/api/custom/action', array('controller' => 'customdataapi', 'action' => 'type'));

//for dynapick
Router::connect('/api/bookmark/put_by_dynapick', array('controller' => 'putbydynapickapi', 'action' => 'index'));

//total device
Router::connect('/api/analytics/device_count', array('controller' => 'devicecount', 'action' => 'index'));

// redirector
Router::connect('/s', array('controller' => 'redirect', 'action' => 'index'));


/**
 * Adding rules for multilanguages
 */
Router::connect('/:language/:controller/:action/*', array(), array('language' => 'eng|cns|cnt|jpn'));
Router::connect('/:language/:controller', array('action' => 'index'), array('language' => 'eng|cns|cnt|jpn'));
Router::connect('/:language', array('controller' => 'pages', 'action' => 'index'), array('language' => 'eng|cns|cnt|jpn'));

Router::connect('/system/:language/:controller/:action/*', array('system' => true), array('language' => 'eng|cns|cnt|jpn'));
Router::connect('/system/:language/:controller', array('action' => 'index', 'system' => true), array('language' => 'eng|cns|cnt|jpn'));
Router::connect('/system/:language', array('controller' => 'pages', 'action' => 'index', 'system' => true), array('language' => 'eng|cns|cnt|jpn'));
/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
require CAKE . 'Config' . DS . 'routes.php';
