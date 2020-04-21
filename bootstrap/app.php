<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$app = new Slim\App([
    'settings' => [
      'displayErrorDetails' => true,
        'db' => [
            'driver'     => $_ENV['DB_CONNECTION'],
            'host'       => $_ENV['DB_HOST'],
            'database'   => $_ENV['DB_DATABASE'],
            'username'   => $_ENV['DB_USERNAME'],
            'password'   => $_ENV['DB_PASSWORD'],
            'charset'      => 'utf8',
            'collation'  => 'utf8_unicode_ci',
            'prefix'       => $_ENV['DB_PREFIX']
        ]
    ]
]);

$container = $app->getContainer();
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['db'] = function($container) use($capsule) {
    return $capsule;
};

$container['auth'] = function($container){
    return new \App\Auth\Auth;
};

// Twig
$container['view'] = function ($container) {
    $view = new Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'debug' => true,
        'cache' => false,
    ]);

    $uri = explode("/",$container['request']->getUri());
    $basePath = $uri[0]."//".$uri[2]."";
    $view->addExtension( new \Slim\Views\TwigExtension(
        $container->router,
        $basePath,
        $container->request->getUri()
    ));

  $view->addExtension(new \Twig_Extension_Debug());
//  $view->addExtension(new Twig_Extensions_Extension_Intl());
  $view['baseUrl'] = $basePath;   
  $view['dirarchivo'] = __DIR__ . '/../public/archivo/';  
  $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response->withStatus(404), 'errors/404.twig');
    };
};

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

header( 'X-Content-Type-Options: nosniff' );
header( 'X-Frame-Options: SAMEORIGIN' );
header( 'X-XSS-Protection: 1;mode=block' );

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        if($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET' || $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: X-Requested-With, X-authentication,Content-Type, X-client');
        }
    }
    exit;
}
require_once __DIR__ . '/CallableControllers.php';
require_once __DIR__ . '/../app/routes.php';
