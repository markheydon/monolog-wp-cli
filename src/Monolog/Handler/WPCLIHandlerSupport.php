<?php

declare(strict_types=1);

namespace MHCG\Monolog\Handler;

use Monolog\Level;

class WPCLIHandlerSupport
{
    /**
     * Merge user logger map overrides over defaults at per-level key depth.
     *
     * @param array $loggerMap Override entries keyed by Monolog level.
     *
     * @return array
     */
    public static function mergeLoggerMapOverrides(array $loggerMap): array
    {
        $defaultMap = self::getDefaultLoggerMap();

        foreach ($loggerMap as $level => $entry) {
            $normalizedLevel = self::normalizeLevel($level);

            if (
                !isset($defaultMap[$normalizedLevel])
                || !is_array($defaultMap[$normalizedLevel])
                || !is_array($entry)
            ) {
                $defaultMap[$normalizedLevel] = $entry;
                continue;
            }

            $defaultMap[$normalizedLevel] = array_replace($defaultMap[$normalizedLevel], $entry);
        }

        return $defaultMap;
    }

    /**
     * Returns a list of supported Logger levels based on the supplied logger map.
     *
     * @param array $map Logger map containing mappings.
     *
     * @return array Array of supported Logger levels.
     */
    public static function getSupportedLevels(array $map): array
    {
        $results = [];
        $levels = array_keys($map);

        foreach ($levels as $level) {
            try {
                $normalizedLevel = self::normalizeLevel($level);
                self::validateLoggerMap(
                    $map,
                    $normalizedLevel,
                    self::getLevelName($normalizedLevel)
                );
                $results[] = $normalizedLevel;
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $results;
    }

    /**
     * Sanity check a logger map.
     *
     * Checks to make sure the logger map contains a supported log level and has an existing method in WP_CLI.
     *
     * @param array $map The logger map to be checked.
     * @param int|string|Level $level The level to be checked.
     * @param string $levelName The level name to use in error messages.
     *
     * @throws \InvalidArgumentException
     */
    public static function validateLoggerMap(array $map, int|string|Level $level, string $levelName = ''): void
    {
        $normalizedLevel = self::toMonologLevel($level);
        $levelValue = $normalizedLevel !== null ? $normalizedLevel->value : (is_int($level) ? $level : (string) $level);
        $entry = self::getLoggerMapEntryForLevel($map, $levelValue);

        if (empty($entry)) {
            throw new \InvalidArgumentException(
                'Logger map has no entry for level ' . $levelName . '(' . $levelValue . ')'
            );
        }

        self::validateLoggerMapEntry($entry, $levelName, $levelValue);
    }

    /**
     * Validates every entry in a logger map.
     *
     * @param array $map The logger map to validate.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function validateAllLoggerMapEntries(array $map): void
    {
        foreach (array_keys($map) as $level) {
            $normalizedLevel = self::normalizeLevel($level);
            self::validateLoggerMap($map, $normalizedLevel, self::getLevelName($normalizedLevel));
        }
    }

    /**
     * Returns the Logger map.
     *
     * @return array Logger map/
     */
    public static function getDefaultLoggerMap(): array
    {
        return [
            Level::Debug->value => [
                'method' => 'debug',
            ],
            Level::Info->value => [
                'method' => 'log',
            ],
            Level::Notice->value => [
                'method' => 'log',
                'includeLevelName' => true,
            ],
            Level::Warning->value => [
                'method' => 'warning',
                'includeLevelName' => true,
            ],
            Level::Error->value => [
                'method' => 'error',
                'includeLevelName' => true,
                'exit' => false,
            ],
            Level::Critical->value => [
                'method' => 'error',
                'includeLevelName' => true,
                'exit' => true,
            ],
            Level::Alert->value => [
                'method' => 'error',
                'includeLevelName' => true,
                'exit' => true,
            ],
            Level::Emergency->value => [
                'method' => 'error',
                'includeLevelName' => true,
                'exit' => true,
            ]
        ];
    }

    /**
     * Convert a level to a string name.
     */
    public static function getLevelName(int|string|Level $level): string
    {
        $normalizedLevel = self::toMonologLevel($level);

        if ($normalizedLevel === null) {
            return 'UNKNOWN';
        }

        return match ($normalizedLevel) {
            Level::Debug => 'DEBUG',
            Level::Info => 'INFO',
            Level::Notice => 'NOTICE',
            Level::Warning => 'WARNING',
            Level::Error => 'ERROR',
            Level::Critical => 'CRITICAL',
            Level::Alert => 'ALERT',
            Level::Emergency => 'EMERGENCY',
        };
    }

    /**
     * Normalize a level value into its integer Monolog level value.
     */
    public static function normalizeLevel(int|string|Level $level): int
    {
        $normalizedLevel = self::toMonologLevel($level);

        if ($normalizedLevel === null) {
            $levelValue = is_int($level) ? $level : (string) $level;
            throw new \InvalidArgumentException(
                'Logger map has no entry for level ' . $levelValue . '(' . $levelValue . ')'
            );
        }

        return $normalizedLevel->value;
    }

    public static function toMonologLevel(int|string|Level $level): ?Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_string($level)) {
            return match (strtolower($level)) {
                'debug' => Level::Debug,
                'info' => Level::Info,
                'notice' => Level::Notice,
                'warning' => Level::Warning,
                'error' => Level::Error,
                'critical' => Level::Critical,
                'alert' => Level::Alert,
                'emergency' => Level::Emergency,
                default => null,
            };
        }

        return match ($level) {
            100 => Level::Debug,
            200 => Level::Info,
            250 => Level::Notice,
            300 => Level::Warning,
            400 => Level::Error,
            500 => Level::Critical,
            550 => Level::Alert,
            600 => Level::Emergency,
            default => null,
        };
    }

    private static function getLoggerMapEntryForLevel(array $map, int|string $levelValue): array
    {
        if (isset($map[(string) $levelValue])) {
            $entry = $map[(string) $levelValue];
            if (is_array($entry)) {
                return $entry;
            }
        }

        foreach ($map as $mapLevel => $mapEntry) {
            if (!is_array($mapEntry)) {
                continue;
            }

            $normalizedMapLevel = self::normalizeLevel($mapLevel);
            if ($normalizedMapLevel === $levelValue) {
                return $mapEntry;
            }
        }

        return [];
    }

    private static function validateLoggerMapEntry(array $entry, string $levelName, int|string $levelValue): void
    {
        if (!method_exists('WP_CLI', $entry['method'])) {
            throw new \InvalidArgumentException(
                'Logger map contains an invalid method for level ' . $levelName . '(' . $levelValue . ')'
            );
        }

        if ($entry['method'] !== 'error' && isset($entry['exit']) && $entry['exit'] === true) {
            throw new \InvalidArgumentException(
                'Logger map for level ' . $levelName . '(' . $levelValue . ') specifies exit but
                         exit is only valid for \'error\' method'
            );
        }
    }
}
