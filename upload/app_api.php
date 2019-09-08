<?php

$app = C::create(__DIR__)->init(7)->createMicro();


if (!empty($_SERVER['HTTP_ORIGIN']) && parse_url($_SERVER['HTTP_ORIGIN'])['host'] != $_SERVER['HTTP_HOST']) {
    // 跨域处理
    header('content-type:application/json;charset=utf8');
    // 指定允许其他域名访问
    header('Access-Control-Allow-Origin:*');
    // 响应类型
    header('Access-Control-Allow-Methods:POST,GET');
    // 响应头设置
    header('Access-Control-Allow-Headers:x-requested-with,content-type');
}

$loader = new Phalcon\Loader();
$loader->registerDirs(
    [
        "../source/apps/app_api/",
    ]
);
$loader->register();

$app->notFound(
    function () use ($app) {
        $app->response->setStatusCode(404, "Not Found");

        $app->response->sendHeaders();

        echo "This is crazy, but this page was not found!";
    }
);

$app->get(
    "/",
    function () {
        echo "<h1>Welcome!</h1>";
    }
);

$app->handle();
