<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;

define('DOFCTC_VERSION', '0.6.5-beta.4');

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

set_time_limit(0);

require dirname(__DIR__).'/vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new LogicException('You need to add "symfony/framework-bundle" as a Composer dependency.');
}

$_SERVER['PROGRAM_DATA'] = trim(str_replace('sess', '', ini_get('session.save_path')), '/\\');
if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'dev.txt')) {
    $_SERVER['APP_DEBUG'] = false;
    $_SERVER['APP_ENV'] = 'prod';
}

$input = new ArgvInput();
if (null !== $env = $input->getParameterOption(['--env', '-e'], null, true)) {
    putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
}

if ($input->hasParameterOption('--no-debug', true)) {
    putenv('APP_DEBUG='.$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
}

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    if (class_exists(Debug::class)) {
        Debug::enable();
    }
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$application = new Application($kernel);
$application->run($input);
