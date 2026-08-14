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
    $autoload = __DIR__ . '/vendor/autoload.php';

    if (!is_readable($autoload)) {
        WP_CLI::error(
            'Composer autoload not found. Run composer install in tests/wordpress/fixtures/monolog-wp-cli-smoke.'
        );

        return;
    }

    require_once $autoload;
}

WP_CLI::add_command('monolog-smoke', static function (array $args, array $assocArgs): void {
    $level = isset($assocArgs['level']) ? (string) $assocArgs['level'] : 'notice';
    $message = isset($assocArgs['message']) ? (string) $assocArgs['message'] : 'smoke';
    $handlerVerbose = !empty($assocArgs['handler-verbose']);
    $context = [];

    if (isset($assocArgs['context-key'])) {
        $context[(string) $assocArgs['context-key']] = isset($assocArgs['context-value'])
            ? (string) $assocArgs['context-value']
            : '';
    }

    $logger = new \Monolog\Logger('monolog-smoke');
    $logger->pushHandler(
        new \MHCG\Monolog\Handler\WPCLIHandler(\Monolog\Level::Debug, true, $handlerVerbose)
    );

    switch ($level) {
        case 'debug':
            $logger->debug($message, $context);
            break;
        case 'info':
            $logger->info($message, $context);
            break;
        case 'notice':
            $logger->notice($message, $context);
            break;
        case 'warning':
            $logger->warning($message, $context);
            break;
        case 'error':
            $logger->error($message, $context);
            break;
        case 'critical':
            $logger->critical($message, $context);
            break;
        default:
            WP_CLI::error('Unsupported level: ' . $level, 2);
            return;
    }

    WP_CLI::success('smoke-command-finished');
});
