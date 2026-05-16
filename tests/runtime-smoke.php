<?php

declare(strict_types=1);

use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Logger;

if (!defined('WP_CLI')) {
    define('WP_CLI', true);
}

if (!class_exists('WP_CLI', false)) {
    class WP_CLI
    {
        public static function debug($message): void
        {
        }

        public static function log($message): void
        {
        }

        public static function warning($message): void
        {
        }

        public static function error($message, $exit = false): void
        {
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

$handler = new WPCLIHandler(Logger::DEBUG);
$logger = new Logger('runtime-smoke');
$logger->pushHandler($handler);
$logger->info('runtime smoke test');

echo "runtime-smoke-ok\n";
