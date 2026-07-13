<?php

declare(strict_types=1);

namespace MHCG\Monolog\Handler;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use WP_CLI;

/**
 * Handler for Monolog that uses WP-CLI methods to for logging.
 *
 * @since 1.0.0
 * @link https://github.com/markheydon/monolog-wp-cli
 * @author Mark Heydon <contact@mhcg.co.uk>
 * @license MIT
 * @package MHCG\Monolog\Handler
 */
class WPCLIHandler extends AbstractProcessingHandler
{
    /** @var string Format used when WP_DEBUG disabled */
    public const WP_CLI_FORMAT_STANDARD = "%message%";
    /** @var string Format used when WP_DEBUG enabled */
    public const WP_CLI_FORMAT_VERBOSE = "%message% %context% %extra%";

    /** @var bool Use verbose style log message format */
    private $verbose = false;

    /** @var array Logger map to use for mapping Logger methods to WP-CLI methods */
    private $loggerMap;

    /**
     * WPCLIHandler constructor.
     *
     * @param int|string|Level $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool $verbose Will use this or WP_DEBUG to include extra information in logging messages
     * @param array|null $loggerMap Optional logger map overrides merged over the defaults
     */
    public function __construct(
        int|string|Level $level = Level::Warning,
        bool $bubble = true,
        bool $verbose = false,
        ?array $loggerMap = null
    ) {
        $isInCLI = (defined('WP_CLI') && WP_CLI);
        if (!$isInCLI) {
            throw new \RuntimeException('');
        }

        parent::__construct($level, $bubble);

        $verbose = (defined('WP_DEBUG') ? WP_DEBUG : false) || $verbose;
        $this->verbose = $verbose;

        if ($loggerMap !== null) {
            $this->loggerMap = self::mergeLoggerMapOverrides($loggerMap);
            self::validateAllLoggerMapEntries($this->loggerMap);
        }
    }

    /**
     * Merge user logger map overrides over defaults at per-level key depth.
     *
     * @param array $loggerMap Override entries keyed by Monolog level.
     *
     * @return array
     */
    private static function mergeLoggerMapOverrides(array $loggerMap): array
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
     * {@inheritdoc}
     */
    public function isHandling(LogRecord $record): bool
    {
        // bodge for debug level as needs to always call that;
        // WP_CLI deals with --debug command argument.
        if ($record->level === Level::Debug) {
            return true;
        }

        // check level is one we know how to handle as more could be added in the future
        // that would need mapping to the WP-CLI:: method.
        $level = $record->level->value;
        $supported = self::getSupportedLevels($this->getLoggerMap());
        $isSupported = in_array($level, $supported, true);

        return $isSupported && parent::isHandling($record);
    }

    /**
     * Returns a list of supported Logger levels based on the supplied logger map.
     *
     * @param array $map Logger map containing mappings.
     *
     * @return array Array of supported Logger levels.
     */
    public static function getSupportedLevels(array $map)
    {
        $results = [];
        // validate the supplied map and return only the valid levels
        $levels = array_keys($map);
        foreach ($levels as $level) {
            try {
                self::validateLoggerMap(
                    $map,
                    self::normalizeLevel($level),
                    self::getLevelName(self::normalizeLevel($level))
                );
                $results[] = $level;
            } catch (\Exception $e) {
                // do nothing
            }
        }
        return $results;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @see https://seldaek.github.io/monolog/doc/message-structure.html
     *
     * @param  LogRecord $record
     *
     * @return void
     * @throws \RuntimeException If a level is passed that is not currently mapped to a WP_CLI:: method.
     * @throws \InvalidArgumentException if something in $record is invalid.
     */
    protected function write(LogRecord $record): void
    {
        // init vars for whatever being used
        $level = self::normalizeLevel($record->level);
        $levelName = self::getLevelName($record->level);
        $formattedMessage = $this->getFormatter()->format($record);

        // build up details of calling method
        $loggerMap = $this->getLoggerMap();
        self::validateLoggerMap($loggerMap, $level, $levelName);

        $method = $loggerMap[$level]['method'];
        $includeLevelName = isset($loggerMap[$level]['includeLevelName'])
            ? (bool)$loggerMap[$level]['includeLevelName'] : false;
        $exit = isset($loggerMap[$level]['exit']) ? (bool)$loggerMap[$level]['exit'] : false;

        if ($includeLevelName) {
            $logMessage = '(' . $levelName . ') ' . $formattedMessage;
        } else {
            $logMessage = $formattedMessage;
        }

        // call it
        if ($method != 'error') {
            WP_CLI::$method($logMessage);
        } else {
            WP_CLI::$method($logMessage, $exit);
        }
    }

    /**
     * Sanity check a logger map.
     *
     * Checks to make sure the logger map contains a supported log level and has an existing method in WP_CLI.
     *
     * @param int $level The level to be checked.
     *
     * @throws \InvalidArgumentException
     */
    private static function getLevelName(int|string|Level $level): string
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

    private static function normalizeLevel(int|string|Level $level): int
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

    private static function toMonologLevel(int|string|Level $level): ?Level
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

    public static function validateLoggerMap(array $map, int|string|Level $level, string $levelName = '')
    {
        $normalizedLevel = self::toMonologLevel($level);
        $levelValue = $normalizedLevel !== null ? $normalizedLevel->value : (is_int($level) ? $level : (string) $level);
        $entry = isset($map[(string)$levelValue]) ? $map[(string)$levelValue] : array();
        if (empty($entry)) {
            throw new \InvalidArgumentException(
                'Logger map has no entry for level ' . $levelName . '(' . $levelValue . ')'
            );
        }
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
    protected function getLoggerMap()
    {
        if (!$this->loggerMap) {
            $this->loggerMap = self::getDefaultLoggerMap();
        }

        return $this->loggerMap;
    }

    /***
     * Returns an array of default mappings to map Logger methods to WP-CLI methods.
     *
     * @return array
     */
    public static function getDefaultLoggerMap()
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
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        if ($this->verbose) {
            return new LineFormatter(self::WP_CLI_FORMAT_VERBOSE);
        } else {
            return new LineFormatter(self::WP_CLI_FORMAT_STANDARD);
        }
    }
}
