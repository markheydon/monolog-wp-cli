<?php

declare(strict_types=1);

use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Level;
use Monolog\Logger;

if (!defined('WP_CLI')) {
    define('WP_CLI', true);
}

class RuntimeSmokeExitException extends \RuntimeException {}

if (!class_exists('WP_CLI', false)) {
    class WP_CLI
    {
        /** @var array<int, array{method:string,message:string,exit:mixed}> */
        private static $calls = [];

        public static function debug($message, $group = false): void
        {
            self::$calls[] = [
                'method' => 'debug',
                'message' => (string) $message,
                'exit' => $group,
            ];
        }

        public static function log($message): void
        {
            self::$calls[] = [
                'method' => 'log',
                'message' => (string) $message,
                'exit' => null,
            ];
        }

        public static function warning($message): void
        {
            self::$calls[] = [
                'method' => 'warning',
                'message' => (string) $message,
                'exit' => null,
            ];
        }

        public static function error($message, $exit = false): void
        {
            self::$calls[] = [
                'method' => 'error',
                'message' => (string) $message,
                'exit' => $exit,
            ];

            if ($exit === true) {
                throw new RuntimeSmokeExitException((string) $message, 1);
            }
        }

        /**
         * @return array<int, array{method:string,message:string,exit:mixed}>
         */
        public static function getCalls(): array
        {
            return self::$calls;
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

function runtimeSmokeAssertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

function runtimeSmokeAssertSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            $message . ' Expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true) . "\n"
        );
        exit(1);
    }
}

function runtimeSmokeAssertContains(string $needle, string $haystack, string $message): void
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$handler = new WPCLIHandler(Level::Debug);
$logger = new Logger('runtime-smoke');
$logger->pushHandler($handler);

$logger->debug('runtime smoke debug');
$logger->info('runtime smoke info');
$logger->notice('runtime smoke notice');
$logger->warning('runtime smoke warning');
$logger->error('runtime smoke error');

try {
    $logger->critical('runtime smoke critical');
    runtimeSmokeAssertTrue(false, 'Critical log should trigger an exit-style exception.');
} catch (RuntimeSmokeExitException $exception) {
    runtimeSmokeAssertSame(1, $exception->getCode(), 'Critical log should use exit code 1.');
}

$calls = WP_CLI::getCalls();
runtimeSmokeAssertSame(6, count($calls), 'Expected one captured WP_CLI call per tested level.');

runtimeSmokeAssertSame('debug', $calls[0]['method'], 'DEBUG should map to WP_CLI::debug().');
runtimeSmokeAssertContains('runtime smoke debug', $calls[0]['message'], 'Missing DEBUG message.');

runtimeSmokeAssertSame('log', $calls[1]['method'], 'INFO should map to WP_CLI::log().');
runtimeSmokeAssertContains('runtime smoke info', $calls[1]['message'], 'Missing INFO message.');

runtimeSmokeAssertSame('log', $calls[2]['method'], 'NOTICE should map to WP_CLI::log().');
runtimeSmokeAssertContains('(NOTICE)', $calls[2]['message'], 'NOTICE should include the level name.');
runtimeSmokeAssertContains('runtime smoke notice', $calls[2]['message'], 'Missing NOTICE message.');

runtimeSmokeAssertSame('warning', $calls[3]['method'], 'WARNING should map to WP_CLI::warning().');
runtimeSmokeAssertContains('(WARNING)', $calls[3]['message'], 'WARNING should include the level name.');
runtimeSmokeAssertContains('runtime smoke warning', $calls[3]['message'], 'Missing WARNING message.');

runtimeSmokeAssertSame('error', $calls[4]['method'], 'ERROR should map to WP_CLI::error().');
runtimeSmokeAssertSame(false, $calls[4]['exit'], 'ERROR should not request exit by default.');
runtimeSmokeAssertContains('(ERROR)', $calls[4]['message'], 'ERROR should include the level name.');
runtimeSmokeAssertContains('runtime smoke error', $calls[4]['message'], 'Missing ERROR message.');

runtimeSmokeAssertSame('error', $calls[5]['method'], 'CRITICAL should map to WP_CLI::error().');
runtimeSmokeAssertSame(true, $calls[5]['exit'], 'CRITICAL should request exit by default.');
runtimeSmokeAssertContains('(CRITICAL)', $calls[5]['message'], 'CRITICAL should include the level name.');
runtimeSmokeAssertContains('runtime smoke critical', $calls[5]['message'], 'Missing CRITICAL message.');

echo "runtime-smoke-ok\n";
