<?php

declare(strict_types=1);

namespace MHCGDev\Monolog\Handler;

use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;

/**
 * Class WPCLIHandlerTest
 *
 * @covers MHCG\Monolog\Handler\WPCLIHandler
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @package MHCGDev\Monolog\Handler
 */
class WPCLIHandlerTest extends TestCase
{
    /** @var string Constant for bodging sanity check */
    public const RUNNING_IN_TEST = 'RunningInTest_RunningInTest';

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('ExitException', false)) {
            class_alias('MHCGDev\Monolog\Stubs\MockExitException', 'ExitException');
        }
        if (!class_exists('WP_CLI', false)) {
            class_alias('MHCGDev\Monolog\Stubs\MockWPCLI', 'WP_CLI');
        }
    }


    //<editor-fold desc="Private helper methods">

    /**
     * Sanity check for all (well most at least) tests.
     *
     * Basically it's around checking if running in WP-CLI or not as unit tests should not be ran in there.
     */
    private function sanityCheck(): void
    {
        $message = 'Unit tests should not be ran from within WP-CLI environment';
        if (defined('WP_CLI')) {
            $this->assertTrue(WP_CLI == self::RUNNING_IN_TEST, $message);
        } else {
            $this->assertFalse(defined('WP_CLI'), $message);
        }
    }

    /**
     * Will need to pretend to be running under WP-CLI for most tests.
     */
    private function pretendToBeInWPCLI(): void
    {
        defined('WP_CLI') || define('WP_CLI', self::RUNNING_IN_TEST);
    }

    /**
     * Fully usable WPCLIHandler object.
     *
     * @return WPCLIHandler
     */
    private static function getHandleObjectForStandardTest(): WPCLIHandler
    {
        return new WPCLIHandler(Level::Debug);
    }

    /**
     * Fully usable Logger object.
     *
     * @return Logger
     */
    private static function getLoggerObjectForStandardTest(): Logger
    {
        return new Logger(self::RUNNING_IN_TEST);
    }

    /**
     * Partial record with level.
     *
     * @param int|string|Level $level
     * @return LogRecord
     */
    private static function getLoggerRecordWithLevel(int|string|Level $level = Level::Debug): LogRecord
    {
        if ($level instanceof Level) {
            $monologLevel = $level;
        } elseif (is_string($level)) {
            $monologLevel = Level::fromName($level);
        } else {
            $monologLevel = Level::fromValue($level);
        }

        return new LogRecord(
            new \DateTimeImmutable(),
            self::RUNNING_IN_TEST,
            $monologLevel,
            '',
            [],
            [],
        );
    }

    //</editor-fold>

    //<editor-fold desc="Constructor Tests">

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorNotInWPCLI()
    {
        $this->sanityCheck();

        $this->expectException(\RuntimeException::class);
        $var = self::getHandleObjectForStandardTest();
        $this->assertTrue(is_object($var));
        unset($var);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorInWPCLI()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $var = self::getHandleObjectForStandardTest();
        $this->assertTrue(is_object($var));
        $this->assertInstanceOf('\MHCG\Monolog\Handler\WPCLIHandler', $var);
        unset($var);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorInWPCLIVerbose()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $var = new WPCLIHandler(Level::Debug, true, true);
        $this->assertTrue(is_object($var));
        $this->assertInstanceOf('\MHCG\Monolog\Handler\WPCLIHandler', $var);
        unset($var);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorInWPCLIWithLoggerMapOverride()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $var = new WPCLIHandler(
            Level::Debug,
            true,
            false,
            [
                Level::Notice->value => [
                    'method' => 'warning',
                    'includeLevelName' => true,
                ],
            ]
        );
        $this->assertTrue(is_object($var));
        $this->assertInstanceOf('\MHCG\Monolog\Handler\WPCLIHandler', $var);
        unset($var);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorRejectsInvalidLoggerMapMethod()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid method');

        new WPCLIHandler(
            Level::Debug,
            true,
            false,
            [
                Level::Notice->value => [
                    'method' => 'method_does_not_exist',
                ],
            ]
        );
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     */
    public function testConstructorRejectsInvalidLoggerMapExit()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('specifies exit');

        new WPCLIHandler(
            Level::Debug,
            true,
            false,
            [
                Level::Notice->value => [
                    'method' => 'log',
                    'includeLevelName' => true,
                    'exit' => true,
                ],
            ]
        );
    }

    /**
     * Tests standard formatter keeps output to the message only.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getFormatter
     */
    public function testStandardFormatterOutputsMessageOnly()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $standard = new WPCLIHandler(Level::Debug, true, false);
        $testRecord = new LogRecord(
            new \DateTimeImmutable(),
            self::RUNNING_IN_TEST,
            Level::Debug,
            'This is a message',
            ['whatever' => 'something'],
            ['whatever2' => 'something else'],
        );

        $testStandard = $standard->getFormatter()->format($testRecord);

        $this->assertSame('This is a message', trim($testStandard));
    }

    /**
     * Tests verbose formatter includes serialized context and extra data.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getFormatter
     */
    public function testVerboseFormatterIncludesContextAndExtra()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $verbose = new WPCLIHandler(Level::Debug, true, true);
        $testRecord = new LogRecord(
            new \DateTimeImmutable(),
            self::RUNNING_IN_TEST,
            Level::Debug,
            'This is a message',
            ['whatever' => 'something'],
            ['whatever2' => 'something else'],
        );

        $testVerbose = $verbose->getFormatter()->format($testRecord);

        $this->assertSame(
            'This is a message {"whatever":"something"} {"whatever2":"something else"}',
            trim($testVerbose)
        );
    }

    /**
     * Tests WP_DEBUG enables verbose formatter output even when constructor verbose is false.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getFormatter
     * @runInSeparateProcess
     */
    public function testWpDebugEnablesVerboseFormatterOutput()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        define('WP_DEBUG', true);

        $handler = new WPCLIHandler(Level::Debug, true, false);
        $testRecord = new LogRecord(
            new \DateTimeImmutable(),
            self::RUNNING_IN_TEST,
            Level::Debug,
            'This is a message',
            ['whatever' => 'something'],
            ['whatever2' => 'something else'],
        );

        $formatted = $handler->getFormatter()->format($testRecord);

        $this->assertSame(
            'This is a message {"whatever":"something"} {"whatever2":"something else"}',
            trim($formatted)
        );
    }

    //</editor-fold>

    //<editor-fold desc="Logger Map Tests">
    /**
     * Tests the default logger map contains all the Logger supported levels.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getDefaultLoggerMap
     */
    public function testDefaultMap()
    {

        // totally round the houses this but Logger doesn't currently return a set of all the level constants
        $loggerLevels = [];
        foreach (Level::VALUES as $levelValue) {
            $loggerLevels[] = $levelValue;
        }
        $loggerMap = WPCLIHandler::getDefaultLoggerMap();
        $difference = array_diff($loggerLevels, array_keys($loggerMap));
        $this->assertCount(
            0,
            $difference,
            'Default logger map is missed some Logger supported levels'
        );
    }

    /**
     * Validates the default -- ours at least should be valid right?
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateLoggerMap
     */
    public function testValidateLoggerMapDefaultMap()
    {
        $defaultMap = WPCLIHandler::getDefaultLoggerMap();
        foreach ($defaultMap as $level => $mapping) {
            $this->assertTrue(count($mapping) > 0);
            $levelName = (string) Level::fromValue($level)->getName();
            // this shouldn't throw an exception
            WPCLIHandler::validateLoggerMap($defaultMap, $level, $levelName);
            $this->assertTrue(true);
        }
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getDefaultLoggerMap
     */
    public function testDefaultMapUsesLogForNotice()
    {
        $defaultMap = WPCLIHandler::getDefaultLoggerMap();

        $this->assertSame('log', $defaultMap[Level::Notice->value]['method']);
        $this->assertTrue($defaultMap[Level::Notice->value]['includeLevelName']);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateLoggerMap
     */
    public function testValidateLoggerMapInvalidLevel()
    {
        $defaultMap = WPCLIHandler::getDefaultLoggerMap();
        try {
            WPCLIHandler::validateLoggerMap($defaultMap, 999999, 'Whatever');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('has no entry for level', $e->getMessage());
        }
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateLoggerMap
     */
    public function testValidateLoggerMapInvalidMethod()
    {
        $map = [
            999999 => [
                'method' => 'method_does_not_exist'
            ]
        ];
        try {
            WPCLIHandler::validateLoggerMap($map, 999999, 'Whatever');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('invalid method', $e->getMessage());
        }
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateLoggerMap
     */
    public function testValidateLoggerMapInvalidUseOfExit()
    {
        $map = [
            Level::Debug->value => [
                'method' => 'debug',
                'exit' => true
            ]
        ];
        try {
            WPCLIHandler::validateLoggerMap(
                $map,
                Level::Debug,
                'DEBUG'
            );
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('specifies exit', $e->getMessage());
        }
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateLoggerMap
     */
    public function testValidateLoggerMapValidUseOfExit()
    {
        $map = [
            Level::Debug->value => [
                'method' => 'error',
                'exit' => true
            ]
        ];
        // shouldn't throw an exception
        WPCLIHandler::validateLoggerMap(
            $map,
            Level::Debug,
            'DEBUG'
        );
        $this->assertTrue(true);
    }

    /**
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::validateAllLoggerMapEntries
     */
    public function testValidateAllLoggerMapEntriesAcceptsDefaultMap()
    {
        WPCLIHandler::validateAllLoggerMapEntries(WPCLIHandler::getDefaultLoggerMap());

        $this->assertTrue(true);
    }
    //</editor-fold>

    //<editor-fold desc="Main Logger Method Tests">

    /**
     * Tests the handler can actually be added to a Logger ok.
     */
    public function testPushHandler()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        unset($logger);
        $this->assertTrue(true);
    }

    //</editor-fold>

    //<editor-fold desc="Handling and Supported Tests">
    /**
     * Tests to make sure string-based level keys are normalised to numeric values.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getSupportedLevels
     */
    public function testSupportedLevelsNormalizesStringKeys()
    {
        $map = [
            'warning' => [
                'method' => 'warning',
            ],
        ];

        $supported = WPCLIHandler::getSupportedLevels($map);

        $this->assertSame([Level::Warning->value], $supported);
    }

    /**
     * Tests to make sure all of the default supported levels are actually showing as supported
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getSupportedLevels
     */
    public function testSupportedDefault()
    {
        $defaultMap = WPCLIHandler::getDefaultLoggerMap();
        $supported = WPCLIHandler::getSupportedLevels($defaultMap);

        $this->assertTrue(count($defaultMap) > 0);
        $this->assertTrue(count($supported) > 0);

        $countOfMap = count(array_keys($defaultMap));
        $this->assertCount($countOfMap, $supported);
        $this->assertEquals(array_keys($defaultMap), $supported);
    }

    /**
     * Tests to make sure the getSupportedLevels methods correctly ignores invalid map entries
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::getSupportedLevels
     */
    public function testSupportedDoesNotIncludeInvalid()
    {
        $map = [
            999999 => [
                'method' => 'method_does_not_exist'
            ],
            Level::Debug->value => [
                'method' => 'debug',
            ]
        ];

        $supported = WPCLIHandler::getSupportedLevels($map);
        $this->assertCount(2, $map);
        $this->assertCount(1, $supported);
        $this->assertEquals($supported[0], Level::Debug->value);
    }

    /**
     * Tests isHandling of WPCLIHandler returns false for a record below the handler level.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingInvalid()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = new WPCLIHandler(Level::Warning);
        $this->assertFalse($handler->isHandling(self::getLoggerRecordWithLevel(Level::Info)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level DEBUG.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidDebug()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Debug)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level INFO.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidInfo()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Info)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level NOTICE.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidNotice()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Notice)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level WARNING.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidWarning()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Warning)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level ERROR.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidError()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Error)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level CRITICAL.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidCritical()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Critical)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level ALERT.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidAlert()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Alert)));
    }

    /**
     * Tests isHandling of WPCLIHandler returns true for support logging level EMERGENCY.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::isHandling
     */
    public function testIsHandlingValidEmergency()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $handler = self::getHandleObjectForStandardTest();
        $this->assertTrue($handler->isHandling(self::getLoggerRecordWithLevel(Level::Emergency)));
    }

    //</editor-fold>

    //<editor-fold desc="Logging method tests">
    /**
     * Test that Logger::debug() doesn't throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerOkForDebug()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $logger->debug('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::info() doesn't throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerOkForInfo()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $logger->info('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::notice() doesn't throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerOkForNotice()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $logger->notice('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::warning() doesn't throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerOkForWarning()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $logger->warning('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::error() doesn't throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerOkForError()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $logger->error('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Smoke test for the README "Example 1 - Basic Concept" usage.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testReadmeExample1BasicConcept()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        \WP_CLI::resetCalls();

        $log = self::getLoggerObjectForStandardTest();
        $log->pushHandler(new WPCLIHandler(Level::Warning));

        $log->warning('This is a warning');
        $log->error('An error has occurred');
        $log->debug('Only shown when running wp with --debug');
        $log->info('General logging - will not be shown when running wp with --quiet');

        unset($log);

        $calls = \WP_CLI::getCalls();
        $this->assertCount(3, $calls);
        $this->assertSame(['warning', 'error', 'debug'], array_column($calls, 'method'));

        $this->assertStringContainsString('This is a warning', $calls[0]['message']);
        $this->assertStringContainsString('An error has occurred', $calls[1]['message']);
        $this->assertStringContainsString('Only shown when running wp with --debug', $calls[2]['message']);

        // INFO is below the handler threshold (WARNING), so it should not be routed.
        $this->assertStringNotContainsString(
            'General logging - will not be shown when running wp with --quiet',
            implode(' ', array_column($calls, 'message'))
        );
    }

    /**
     * Test that NOTICE now routes through WP_CLI::log by default.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerRoutesNoticeToLogByDefault()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        \WP_CLI::resetCalls();

        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());
        $logger->notice('Something normal but significant happened');

        $calls = \WP_CLI::getCalls();

        $this->assertCount(1, $calls);
        $this->assertSame('log', $calls[0]['method']);
        $this->assertStringContainsString('(NOTICE)', $calls[0]['message']);
        $this->assertStringContainsString('Something normal but significant happened', $calls[0]['message']);

        unset($logger);
    }

    /**
     * Test that ERROR routes through WP_CLI::error without requesting exit.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerRoutesErrorWithoutExitByDefault()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        \WP_CLI::resetCalls();

        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());
        $logger->error('Recoverable error');

        $calls = \WP_CLI::getCalls();

        $this->assertCount(1, $calls);
        $this->assertSame('error', $calls[0]['method']);
        $this->assertFalse($calls[0]['exit']);
        $this->assertStringContainsString('(ERROR)', $calls[0]['message']);
        $this->assertStringContainsString('Recoverable error', $calls[0]['message']);

        unset($logger);
    }

    /**
     * Test that CRITICAL routes through WP_CLI::error and requests exit.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerRoutesCriticalToErrorAndExits()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        \WP_CLI::resetCalls();

        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        try {
            $logger->critical('Critical failure');
            $this->fail('Expected critical logging to request exit.');
        } catch (\MHCGDev\Monolog\Stubs\MockExitException $exception) {
            $this->assertSame(1, $exception->getCode());
        }

        $calls = \WP_CLI::getCalls();

        $this->assertCount(1, $calls);
        $this->assertSame('error', $calls[0]['method']);
        $this->assertTrue($calls[0]['exit']);
        $this->assertStringContainsString('(CRITICAL)', $calls[0]['message']);
        $this->assertStringContainsString('Critical failure', $calls[0]['message']);

        unset($logger);
    }

    /**
     * Test that a partial custom logger map is merged over the defaults.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::__construct
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function testHandlerMergesPartialCustomLoggerMap()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        \WP_CLI::resetCalls();

        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(
            new WPCLIHandler(
                Level::Debug,
                true,
                false,
                [
                    Level::Info->value => [
                        'method' => 'debug',
                        'includeLevelName' => true,
                    ],
                ]
            )
        );

        $logger->info('Custom info mapping');
        $logger->notice('Default notice mapping remains');

        $calls = \WP_CLI::getCalls();

        $this->assertCount(2, $calls);
        $this->assertSame('debug', $calls[0]['method']);
        $this->assertStringContainsString('(INFO)', $calls[0]['message']);
        $this->assertSame('log', $calls[1]['method']);
        $this->assertStringContainsString('(NOTICE)', $calls[1]['message']);

        unset($logger);
    }

    /**
     * Test that Logger::critical() DOES throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function disabledtestHandlerOkForCritical()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $this->expectException('ExitException');
        $logger->critical('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::alert() DOES throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function disabledtestHandlerOkForAlert()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $this->expectException('ExitException');
        $logger->alert('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }

    /**
     * Test that Logger::emergency() DOES throw an error using WPCLIHander.
     *
     * @covers \MHCG\Monolog\Handler\WPCLIHandler::write
     */
    public function disabledtestHandlerOkForEmergency()
    {
        $this->sanityCheck();

        $this->pretendToBeInWPCLI();
        $logger = self::getLoggerObjectForStandardTest();
        $logger->pushHandler(self::getHandleObjectForStandardTest());

        $this->expectException('ExitException');
        $logger->emergency('This is the end...');

        unset($logger);
        $this->assertTrue(true);
    }
    //</editor-fold>
}
