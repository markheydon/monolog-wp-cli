<?php

declare(strict_types=1);

namespace MHCGDev\Monolog\Stubs;

/**
 * Mock of the WP_CLI class for testing.
 *
 * @package MHCGDev\Monolog\Stubs
 */
class MockWPCLI
{
    /** @var array<int, array{method:string,message:string,exit:mixed}> */
    private static $calls = [];

    //<editor-fold desc="Magic Methods">
    public function __call($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        $message = "Mock object method not implemented '$name' "
            . implode(', ', $arguments) . "\n";
        throw new \RuntimeException($message);
    }

    public static function __callStatic($name, $arguments)
    {
        // Note: value of $name is case sensitive.
        $message = "Mock static method not implemented '$name' "
            . implode(', ', $arguments) . "\n";
        throw new \RuntimeException($message);
    }
    //</editor-fold>

    public static function log($message)
    {
        self::$calls[] = [
            'method' => 'log',
            'message' => (string) $message,
            'exit' => null,
        ];
        return;
    }

    public static function success($message)
    {
        self::$calls[] = [
            'method' => 'success',
            'message' => (string) $message,
            'exit' => null,
        ];
        return;
    }

    public static function debug($message, $group = false)
    {
        self::$calls[] = [
            'method' => 'debug',
            'message' => (string) $message,
            'exit' => $group,
        ];
        return;
    }

    public static function warning($message)
    {
        self::$calls[] = [
            'method' => 'warning',
            'message' => (string) $message,
            'exit' => null,
        ];
        return;
    }

    public static function error($message, $exit = true)
    {
        self::$calls[] = [
            'method' => 'error',
            'message' => (string) $message,
            'exit' => $exit,
        ];

        // taken from class-wp-cli.php file
        $return_code = false;
        if (true === $exit) {
            $return_code = 1;
        } elseif (is_int($exit) && $exit >= 1) {
            $return_code = $exit;
        }

        if ($return_code) {
            throw new MockExitException('', $return_code);
        }
    }

    /**
     * Reset captured calls between tests.
     */
    public static function resetCalls()
    {
        self::$calls = [];
    }

    /**
     * Return captured static method calls.
     *
     * @return array<int, array{method:string,message:string,exit:mixed}>
     */
    public static function getCalls()
    {
        return self::$calls;
    }
}
