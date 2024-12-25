<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}


/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Errors::show404');
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->get('/', 'HomeController::index', ['filter' => 'userAuth']);

/*
 * --------------------------------------------------------------------
 * Authentication
 * --------------------------------------------------------------------
 */

$routes->get('/login', 'Authentication::index', ['filter' => 'userNoAuth']);
$routes->post('/login', 'Authentication::login', ['filter' => 'userNoAuth']);
$routes->get('/auth-register', 'Authentication::authRegister', ['filter' => 'userNoAuth']);
$routes->post('/register', 'Authentication::register', ['filter' => 'userNoAuth']);
$routes->get('/logout', 'Authentication::logout', ['filter' => 'userAuth']);

$routes->get('/auth/login/(:any)', 'Authentication::loginByPlamform/$1');

/*
 * --------------------------------------------------------------------
 * Authentication Social
 * --------------------------------------------------------------------
 */

$routes->get('/callback/(:any)', 'CallbackController::handle/$1');

// -----------------------------------------------------------------------------
// Chat & Message
// -----------------------------------------------------------------------------
$routes->get('/chat', 'ChatController::index'); // หน้าแสดงรายการห้องสนทนา
$routes->get('/chatLeft', 'ChatController::messageLeft'); // หน้าแสดงรายการห้องสนทนา ด้านซ้าย
$routes->get('/messages/(:num)', 'ChatController::fetchMessages/$1'); // ดึงข้อความจากห้องสนทนา
$routes->post('/send-message', 'ChatController::sendMessage'); // ส่งข้อความไปยัง WebSocket

// -----------------------------------------------------------------------------
// Setting
// -----------------------------------------------------------------------------
$routes->get('/setting', 'SettingController::index');
$routes->post('/setting', 'SettingController::setting');
$routes->post('/check/connection', 'SettingController::connection'); // เช็คการเชื่อมต่อ
$routes->post('/remove-social', 'SettingController::removeSocial'); // ลบ User Social
$routes->post('/setting/save-token', 'SettingController::saveToken'); // ระบุ Token ใช้กรณี Facebook
$routes->post('/setting/ai', 'SettingController::settingAI'); // ตั้งค่าสถานะการใช้ AI ช่วยตอบ

$routes->get('/setting-new', 'SettingController::index');

// registration/channelConnection

// -----------------------------------------------------------------------------
// Webhook
// -----------------------------------------------------------------------------
$routes->get('/webhook', 'WebhookController::verifyWebhook'); // Webhook สำหรับยืนยัน Meta Developer
$routes->post('/webhook', 'WebhookController::webhook'); // Webhook สำหรับรับข้อมูลจากแพลตฟอร์ม


$routes->get('/callback', 'OauthController::callback');

$routes->get('/auth/FbPagesList', 'AuthController::FbPagesList');
$routes->get('/auth/WABListBusinessAccounts', 'AuthController::WABListBusinessAccounts');

$routes->post('/connect/connectToApp', 'ConnectController::connectToApp');

/*
 * --------------------------------------------------------------------
 * Helper
 * --------------------------------------------------------------------
 */

 $routes->get('/check/token/(:any)', 'OauthController::checkToken/$1');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
