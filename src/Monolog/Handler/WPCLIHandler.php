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
        if (!defined('WP_CLI') || !WP_CLI) {
            throw new \RuntimeException('');
        }

        parent::__construct($level, $bubble);

        $this->verbose = (defined('WP_DEBUG') ? WP_DEBUG : false) || $verbose;

        if ($loggerMap !== null) {
            $this->loggerMap = WPCLIHandlerSupport::mergeLoggerMapOverrides($loggerMap);
            WPCLIHandlerSupport::validateAllLoggerMapEntries($this->loggerMap);
        }
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
        return WPCLIHandlerSupport::getSupportedLevels($map);
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
        $level = self::normalizeLevel($record->level);
        $levelName = self::getLevelName($record->level);
        $loggerMap = $this->getLoggerMap();
        $formattedMessage = $this->getFormatter()->format($record);

        self::validateLoggerMap($loggerMap, $level, $levelName);

        $method = $loggerMap[$level]['method'];
        $logMessage = isset($loggerMap[$level]['includeLevelName']) && (bool) $loggerMap[$level]['includeLevelName']
            ? '(' . $levelName . ') ' . $formattedMessage
            : $formattedMessage;
        $exit = isset($loggerMap[$level]['exit']) ? (bool) $loggerMap[$level]['exit'] : false;

        if ($method !== 'error') {
            WP_CLI::$method($logMessage);
            return;
        }

        WP_CLI::$method($logMessage, $exit);
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
    public static function validateLoggerMap(array $map, int|string|Level $level, string $levelName = '')
    {
        WPCLIHandlerSupport::validateLoggerMap($map, $level, $levelName);
    }

    public static function normalizeLevel(int|string|Level $level): int
    {
        return WPCLIHandlerSupport::normalizeLevel($level);
    }

    public static function getLevelName(int|string|Level $level): string
    {
        return WPCLIHandlerSupport::getLevelName($level);
    }

    public static function toMonologLevel(int|string|Level $level): ?Level
    {
        return WPCLIHandlerSupport::toMonologLevel($level);
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
        WPCLIHandlerSupport::validateAllLoggerMapEntries($map);
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
        return WPCLIHandlerSupport::getDefaultLoggerMap();
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
