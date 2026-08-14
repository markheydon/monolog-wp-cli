# PSR-3 to WP-CLI Level Mapping

This note records the default level mapping implemented by `MHCG\Monolog\Handler\WPCLIHandler::getDefaultLoggerMap()` after the issue `#15` change set, and captures related behaviour for optional mapping overrides.

## Sources reviewed

- Current implementation: `/src/Monolog/Handler/WPCLIHandler.php`
- Current tests: `/tests/Monolog/Handler/WPCLIHandlerTest.php`
- PSR-3 level semantics: `php-fig/fig-standards` `accepted/PSR-3-logger-interface.md`
- WP-CLI output semantics: `wp-cli/wp-cli` `php/class-wp-cli.php`
- Follow-up discussion: issue `#15`

## WP-CLI method semantics that matter here

- `WP_CLI::debug()` is for debug detail and only appears when `--debug` is enabled.
- `WP_CLI::log()` is the normal informational channel and is suppressed by `--quiet`.
- `WP_CLI::warning()` is explicitly a warning channel, is prefixed with `Warning:`, and is suppressed by `--quiet`.
- `WP_CLI::error()` is the failure channel. It writes to STDERR and exits by default unless passed `false`.
- `WP_CLI::success()` is not a PSR-3 severity level. It represents successful completion, not a log severity.
- `WP_CLI::line()` ignores `--quiet`, so it is not a good default target for normal PSR-3 logging.

## Implemented default mapping (v2.2)

| PSR-3 level | PSR-3 intent | Implemented mapping | Notes |
| --- | --- | --- | --- |
| `DEBUG` | Detailed debug information | `debug` | Strong fit. Visibility remains controlled by `--debug`. |
| `INFO` | Interesting events | `log` | Strong fit. Suppressed by `--quiet`. |
| `NOTICE` | Normal but significant events | `log` + level name | Updated for issue `#15`. Avoids over-reporting as warning while preserving distinction from INFO. |
| `WARNING` | Exceptional occurrences that are not errors | `warning` + level name | Strong fit. |
| `ERROR` | Runtime errors that should be logged and monitored | `error` + level name + `exit=false` | Preserves error channel without forced exit. |
| `CRITICAL` | Critical conditions | `error` + level name + `exit=true` | `error` is the closest channel; `exit=true` is handler policy. |
| `ALERT` | Action must be taken immediately | `error` + level name + `exit=true` | Same policy caveat as CRITICAL. |
| `EMERGENCY` | System is unusable | `error` + level name + `exit=true` | Same policy caveat as CRITICAL. |

## Review of the current strategy

### What aligns well with PSR-3

- `DEBUG -> debug` is a strong match.
- `INFO -> log` is a strong match.
- `WARNING -> warning` is a strong match.
- `ERROR -> error` is a strong match.
- Mapping `CRITICAL`, `ALERT`, and `EMERGENCY` to `error` is reasonable because WP-CLI does not provide higher-severity output methods.
- Including the PSR-3 level name once multiple PSR-3 levels share the same WP-CLI method is useful and should stay.

### What was changed

- `NOTICE` now maps to `log` instead of `warning`.
- `includeLevelName` remains enabled for `NOTICE`.
- Override support was added to `WPCLIHandler` constructor via optional logger-map input, merged over defaults.
- Override entries are validated in constructor so invalid mappings fail early.

### Important ambiguity

- `exit=true` for `CRITICAL`, `ALERT`, and `EMERGENCY` may be a sensible package default for CLI commands, but it is better described as a **handler policy** than as part of the PSR-3-to-WP-CLI level mapping itself.
- In other words: `CRITICAL+ -> error` is the level mapping; “should this log call terminate the command?” is a second decision layered on top.

## Current default map snapshot

The current default map is:

```php
[
    Logger::DEBUG => ['method' => 'debug'],
    Logger::INFO => ['method' => 'log'],
    Logger::NOTICE => ['method' => 'log', 'includeLevelName' => true],
    Logger::WARNING => ['method' => 'warning', 'includeLevelName' => true],
    Logger::ERROR => ['method' => 'error', 'includeLevelName' => true, 'exit' => false],
    Logger::CRITICAL => ['method' => 'error', 'includeLevelName' => true, 'exit' => true],
    Logger::ALERT => ['method' => 'error', 'includeLevelName' => true, 'exit' => true],
    Logger::EMERGENCY => ['method' => 'error', 'includeLevelName' => true, 'exit' => true],
]
```

## Backwards compatibility path

Consumers that relied on legacy `NOTICE -> warning` behaviour can restore it by passing a partial override map to `WPCLIHandler`.

```php
[
    Logger::NOTICE => ['method' => 'warning', 'includeLevelName' => true],
]
```

This override is merged with the defaults; consumers do not need to re-declare every level mapping.

## Status for issue #15

- `NOTICE` mismatch is resolved in code and tests.
- Optional override support is available for consumers who want custom mappings.
- `CRITICAL+` exit policy remains unchanged and should continue to be treated as handler policy rather than pure level mapping semantics.
