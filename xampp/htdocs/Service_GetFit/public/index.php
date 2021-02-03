<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

// use Kreait\Firebase\Factory;
// use Kreait\Firebase\ServiceAccount;

require __DIR__ . '/../vendor/autoload.php';

// $serviceAccount=ServiceAccount::fromJsonFile(__DIR__.'\secret\tugasakhir-273202-6ee1f9786c82.json');

// $firebase=(new Factory)
//     ->withServiceAccount($serviceAccount)
//     ->create();

// $database=$firebase->getDatabase();

// $factory = (new Factory)->withServiceAccount(__DIR__. '\secret\tugasakhir-273202-6ee1f9786c82.json');
        
// $database = $factory->createDatabase();

// $this->database=$database;

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Run app
$app->run();
