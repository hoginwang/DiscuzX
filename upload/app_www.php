<?php
error_reporting(E_ALL);                              // 输出异常

define('BASE_PATH'   , dirname(__DIR__));            // BASE PATH
define('APP_PATH'    , BASE_PATH . '/source/apps/app_www');     // APP PATH


define('SOURCE_PATH'  , BASE_PATH   . '/source');  // 源文件根目录，如果不设置，默认在 DIDCUZ_ROOT 的上一级下面
define('TABLE_PATH'   , SOURCE_PATH . '/tables');  // table类，如果不设置，默认在 SOURCE_PATH 下面。




// 注册服务
$di = new Phalcon\Di();

$di->setShared('router', function () {
    $router = new Phalcon\Pod\Mvc\Router('modules');
    //$router->setDefaultModule('index');
    return $router;
});

$di->setShared('response', function () {
    $response = new Phalcon\Http\Response();
    return $response;
});




// 创建pod默认工厂应用, 可以在不同模块的 __constractor 初始化不同深度的 init。$application = C::create(__DIR__)->createApplication($di);
$application = C::create(__DIR__)->init(7)->createApplication($di);




// 注册模块
$application->registerModules([
    'index' => [
        'className' => 'Modules\Index\Module',
        'path'      => '../source/apps/app_www/modules/index/Module.php'
    ],
    'frontend'  => [
        'className' => 'Modules\Frontend\Module',
        'path'      => '../source/apps/app_www/modules/frontend/Module.php'
    ],
    'news'  => [
        'className' => 'Modules\News\Module',
        'path'      => '../source/apps/app_www/modules/news/Module.php'
    ]
]);

// 分发
echo $application->handle()->getContent();

