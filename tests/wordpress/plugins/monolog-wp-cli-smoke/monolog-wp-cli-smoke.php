<?php

/**
 * Plugin Name: Monolog WP-CLI Smoke Fixture
 * Description: WP-CLI command fixture for monolog-wp-cli integration smoke testing.
 * Version: 0.1.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

if (!class_exists('MHCG\\Monolog\\Handler\\WPCLIHandler')) {
    require_once '/workspaces/monolog-wp-cli/vendor/autoload.php';
}

use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Logger;

WP_CLI::add_command('monolog-smoke', static function (array $args, array $assocArgs): void {
    $level = isset($assocArgs['level']) ? (string) $assocArgs['level'] : 'notice';
    $message = isset($assocArgs['message']) ? (string) $assocArgs['message'] : 'smoke';

    $logger = new Logger('monolog-smoke');
    $logger->pushHandler(new WPCLIHandler(Logger::DEBUG));

    switch ($level) {
        case 'debug':
            $logger->debug($message);
            break;
        case 'info':
            $logger->info($message);
            break;
        case 'notice':
            $logger->notice($message);
            break;
        case 'warning':
            $logger->warning($message);
            break;
        case 'error':
            $logger->error($message);
            break;
        case 'critical':
            $logger->critical($message);
            break;
        default:
            WP_CLI::error('Unsupported level: ' . $level, 2);
            return;
    }

    WP_CLI::success('smoke-command-finished');
});
