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

$routes->get('/test/gpt', 'TestController::index');

$routes->get('/', 'Authentication::index', ['filter' => 'userNoAuth']); // หน้าแรก
$routes->get('/dashboard', 'HomeController::index', ['filter' => ['userAuth', 'checkPermissions:dashboard']]);
$routes->get('/policy', 'HomeController::policy');

/*
 * --------------------------------------------------------------------
 * Authentication
 * --------------------------------------------------------------------
 */

$routes->get('/login', 'Authentication::index', ['filter' => 'userNoAuth']); // หน้าแรก
$routes->get('/password', 'Authentication::password', ['filter' => 'userNoAuth']); // หน้า login 
$routes->post('/login', 'Authentication::login', ['filter' => 'userNoAuth']); // ทำการ login
$routes->get('/auth-register', 'Authentication::authRegister', ['filter' => 'userNoAuth']); // หน้าสมัครสมาชิก
$routes->post('/register', 'Authentication::register', ['filter' => 'userNoAuth']); // ทำการสมัครสมาชิก
$routes->get('/logout', 'Authentication::logout'); // ออกจากระบบ

/*
 * --------------------------------------------------------------------
 * Authentication Social
 * --------------------------------------------------------------------
 */

$routes->get('/auth/login/(:any)', 'Authentication::loginByPlamform/$1');
$routes->get('/auth/callback/(:any)', 'Authentication::authCallback/$1');

// -----------------------------------------------------------------------------
// Team
// -----------------------------------------------------------------------------

$routes->group('team', ['filter' => ['userAuth', 'checkPermissions:team']], function ($routes) {
    $routes->get('/', 'TeamController::index');
    $routes->post('create', 'TeamController::create');
    $routes->post('invite-to-member', 'TeamController::inviteToTeamMember');
    $routes->get('getTeam/(:any)', 'TeamController::getTeam/$1');
    $routes->post('update', 'TeamController::update');
    $routes->post('destroy', 'TeamController::destroy');
});

$routes->get('/inviteToTeamMember/(:any)', 'TeamController::viewInviteToTeamMember/$1');

// -----------------------------------------------------------------------------
// Profile
// -----------------------------------------------------------------------------

$routes->group('profile', ['filter' => ['userAuth', 'checkPermissions:profile']], function ($routes) {
    $routes->get('/', 'ProfileController::index');
    $routes->get('get-free-request-limit', 'ProfileController::getFreeRequestLimit');
});

// -----------------------------------------------------------------------------
// Help
// -----------------------------------------------------------------------------

$routes->group('help', ['filter' => 'userAuth'], function ($routes) {
    $routes->get('/', 'HelpController::index');
});

// -----------------------------------------------------------------------------
// Subscription
// -----------------------------------------------------------------------------

$routes->group('subscription', ['filter' => ['userAuth', 'checkPermissions:payment']], function ($routes) {
    $routes->post('selectPlan', 'SubscriptionController::selectPlan');
    $routes->post('handlePlan', 'SubscriptionController::handlePlan');
});

// -----------------------------------------------------------------------------
// Payment
// -----------------------------------------------------------------------------

$routes->group('payment', ['filter' => ['userAuth', 'checkPermissions:payment']], function ($routes) {
    $routes->get('success', 'PaymentController::success');
    $routes->get('cancel', 'PaymentController::cancel');
});

// -----------------------------------------------------------------------------
// Chat & Message
// -----------------------------------------------------------------------------

$routes->get('/chat', 'ChatController::index', ['filter' => ['userAuth', 'checkPermissions:chat']]); // หน้าแสดงรายการห้องสนทนา
$routes->get('/chatLeft', 'ChatController::messageLeft', ['filter' => 'userAuth']); // หน้าแสดงรายการห้องสนทนา ด้านซ้าย
$routes->get('/messages/(:num)', 'ChatController::fetchMessages/$1', ['filter' => 'userAuth']); // ดึงข้อความจากห้องสนทนา
$routes->post('/send-message', 'ChatController::sendMessage', ['filter' => 'userAuth']); // ส่งข้อความไปยัง WebSocket

// -----------------------------------------------------------------------------
// Setting
// -----------------------------------------------------------------------------

$routes->group('setting', ['filter' => ['userAuth', 'checkPermissions:setting']], function ($routes) {
    $routes->post('/', 'SettingController::setting', ['filter' => 'userCheckPackagePermission:connect']);
    $routes->get('connect', 'SettingController::index');
    $routes->get('message', 'SettingController::index_message');
    $routes->post('save-token', 'SettingController::saveToken'); // ระบุ Token ใช้กรณี Facebook
    $routes->post('ai', 'SettingController::settingAI'); // ตั้งค่าสถานะการใช้ AI ช่วยตอบ
});

$routes->post('/check/connection', 'SettingController::connection'); // เช็คการเชื่อมต่อ
$routes->post('/remove-social', 'SettingController::removeSocial'); // ลบ User Social
$routes->post('/message-traning', 'SettingController::message_traning'); // traning message by user   
$routes->get('/message-traning-load/(:any)', 'SettingController::message_traning_load/$1');
$routes->post('/message-traning-testing', 'SettingController::message_traning_testing');
$routes->post('/message-traning-clears', 'SettingController::message_traning_clears');

// -----------------------------------------------------------------------------
// Webhook
// -----------------------------------------------------------------------------

// Meta & Line
$routes->get('/webhook/(:any)', 'WebhookController::verifyWebhook/$1'); // Webhook สำหรับยืนยัน Meta Developer
$routes->post('/webhook/(:any)', 'WebhookController::webhook/$1'); // Webhook สำหรับรับข้อมูลจากแพลตฟอร์ม

// Stripe
$routes->post('/stripe/webhook/', 'StripeController::webhook'); // Webhook สำหรับรับข้อมูลจากแพลตฟอร์ม

// -----------------------------------------------------------------------------
// Helper
// -----------------------------------------------------------------------------

$routes->get('/callback/(:any)', 'CallbackController::callback/$1');

$routes->get('/check/token/(:any)', 'AuthController::checkToken/$1');
$routes->get('/auth/FbPagesList', 'AuthController::FbPagesList');
$routes->get('/auth/WABListBusinessAccounts', 'AuthController::WABListBusinessAccounts');
// $routes->get('/auth/IGListBusinessAccounts', 'AuthController::IGListBusinessAccounts');

$routes->post('/connect/connectToApp', 'ConnectController::connectToApp');

/*
 * --------------------------------------------------------------------
 * CRONJOB
 * --------------------------------------------------------------------
 */

// Reset free request
$routes->cli('cronjob/reset-free-request-limit', 'Reset::run', ['namespace' => 'App\Controllers\cronjob']);

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
