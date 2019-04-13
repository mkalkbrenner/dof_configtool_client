<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

define('DOFCTC_VERSION', '0.2.0');

require dirname(__DIR__).'/config/bootstrap.php';

if (isset($_SERVER['PHPDESKTOP_VERSION'])) {
    $_SERVER['PROGRAM_DATA'] = trim(str_replace('sess', '', ini_get('session.save_path')), '/\\');
    if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'dev.txt')) {
        $_SERVER['APP_DEBUG'] = false;
        $_SERVER['APP_ENV'] = 'prod';
    }
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
