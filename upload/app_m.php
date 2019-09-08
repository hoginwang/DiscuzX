<?php
error_reporting(E_ALL);                                       // 输出异常

define('BASE_PATH'   , dirname(__DIR__));                     // BASE PATH
define('APP_PATH'    , BASE_PATH . '/source/apps/app_m');     // APP PATH


define('SOURCE_PATH'  , BASE_PATH   . '/source');  // 源文件根目录，如果不设置，默认在 DIDCUZ_ROOT 的上一级下面
define('TABLE_PATH'   , SOURCE_PATH . '/tables');  // table类，如果不设置，默认在 SOURCE_PATH 下面。


include SOURCE_PATH . '/function/function.php';


$loader = new Phalcon\Loader();

$loader->registerDirs(
    [
        "../source/apps/app_m/controllers/",
        "../source/apps/app_m/models/",
    ]
);

$loader->register();

$di = new Phalcon\DI();

// Registering a router
$di->set(
    "router",
    function () {
        return new Phalcon\Pod\Mvc\Router('simple');
    }
);


// Registering a dispatcher
$di->set(
    "dispatcher",
    function () {
        return new Phalcon\Mvc\Dispatcher();
    }
);

// Registering a Http\Response
$di->set(
    "response",
    function () {
        return new Phalcon\Http\Response();
    }
);

// Registering a Http\Request
$di->set(
    "request",
    function () {
        return new Phalcon\Http\Request();
    }
);

// static.myGlobalStaticFunction(foo, bar); {# global static #}
$di->setShared("static", new Phalcon\Pod\StaticWrapper());

// Registering the view component
$di->set(
    "view",
    function () {
        $view = new Phalcon\Mvc\View();
        $view->setViewsDir("../source/apps/app_m/views/");
        $view->registerEngines([

            '.volt'  => function($view, $di) {
                $volt = new Phalcon\Mvc\View\Engine\Volt($view, $di);
                $volt->setOptions(array(
                    'compiledPath' => '../source/apps/app_m/cache/',
                    //'compiledExtension' => '.compiled',
                    'compileAlways' => true,
                    'stat' => true
                ));

                return $volt;
            }
        ]);
        return $view;
    }
);

echo C::create(__DIR__)->init(7)->createApplication($di)->handle()->getContent();