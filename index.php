<?php

session_start();

define('BASE_DIR', __DIR__ .'/');
define('ENDPOINTS_DIR', BASE_DIR . '/Endpoints');
define('MODELS_DIR', BASE_DIR . '/Models');
define('VENDOR_DIR', BASE_DIR . '/vendor');
define('CONFIG_DIR', BASE_DIR . '/Config');

require_once CONFIG_DIR . '/ErrorCodes.php';

require_once __DIR__ . '/vendor/AutoLoad.php';


use SariwonAPI\Config\Configuration;
use SariwonAPI\Config\ErrorCodes;

$config = Configuration::getInstance();

$url = explode('/', $_GET['url']);

if ($config->get('core', 'debug/show_err') == true) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

function jsonResponse(bool $success, mixed $data, mixed $err = null, int $status = 200): never {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    if (!$success) {
        $data = [
            'code' => $err ?? ErrorCodes::UNKNOWN_ERROR,
            'message' => ErrorCodes::getMessage($err ?? ErrorCodes::UNKNOWN_ERROR),
        ];
    }
    echo json_encode(['success' => $success, 'status' => $status, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($config->get('core', 'debug/lock_access') == true) {
    jsonResponse(false, [], ErrorCodes::LOCKED_ACCESS, 403);
    die();
}

if ($config->get('core', 'debug/show_ep') == true && $url[0] == "__debug__") {

    $epFolder = '../api/Endpoints';
    $namespace = 'SariwonAPI\\Endpoints\\';
    $eps = [];

    foreach (scandir($epFolder) as $fileName) {
        if ($fileName === '.' || $fileName === '..') {
            continue;
        }

        $fullPath = $epFolder . '/' . $fileName;

        if (is_file($fullPath) && pathinfo($fileName, PATHINFO_EXTENSION) === 'php') {
            require_once $fullPath;
        }
    }

    foreach (scandir($epFolder) as $fileName) {
        if ($fileName === '.' || $fileName === '..') {
            continue;
        }

        if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $className = $namespace . pathinfo($fileName, PATHINFO_FILENAME);

        if (
            class_exists($className) &&
            is_subclass_of($className, \SariwonAPI\Endpoints\AbstractEndpoint::class)
        ) {
            $eps = array_merge($eps, $className::getEndpoints());
        }
    }

    require_once('View/EndpointsDebug.php');
    die();

}

try {
    $requestMethod = strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');
    $requestUrl = trim($_GET['url'] ?? '', '/');

    // Liste des classes endpoint à exposer
    $endpointClasses = [
        SariwonAPI\Endpoints\Auth::class,
        SariwonAPI\Endpoints\Character::class,
        SariwonAPI\Endpoints\Map::class,
        SariwonAPI\Endpoints\Me::class,
        SariwonAPI\Endpoints\Server::class,
        SariwonAPI\Endpoints\User::class,
    ];

    $routes = [];

    foreach ($endpointClasses as $endpointClass) {
        $routes = array_merge($routes, $endpointClass::getEndpoints());
    }

    foreach ($routes as $route) {
        if ($route['method'] !== $requestMethod) {
            continue;
        }

        $pattern = trim($route['url'], '/');

        // Transformation des paramètres dynamiques
        // ex: users/{id} => #^users/([^/]+)$#
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $requestUrl, $matches)) {
            continue;
        }

        $params = array_filter(
            $matches,
            fn($key) => !is_int($key),
            ARRAY_FILTER_USE_KEY
        );

        $className = 'SariwonAPI\\Endpoints\\' . $route['base'];

        if (!class_exists($className)) {
            jsonResponse(false, [], ErrorCodes::ENDPOINT_NOT_FOUND, 404);
        }

        $instance = new $className();

        if (!method_exists($instance, $route['handler'])) {
            jsonResponse(false, [], ErrorCodes::INVALID_ENDPOINT_METHOD, 404);
        }

        $response = $instance->{$route['handler']}(...array_values($params));

        jsonResponse(true, $response);
    }

    jsonResponse(false, [], ErrorCodes::ENDPOINT_NOT_FOUND, 404);

} catch (Throwable $e) {

    jsonResponse(false, [], ErrorCodes::INTERNAL_SERVER_ERROR, 500);
    
}