# PSR-3 to WP-CLI Level Mapping

This note records the default level mapping currently implemented by `MHCG\Monolog\Handler\WPCLIHandler::getDefaultLoggerMap()` and reviews whether it matches the intent of PSR-3 and the available WP-CLI output methods.

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

## Current and recommended mapping

| PSR-3 level | PSR-3 intent | Current codebase mapping | Recommended mapping | Notes |
| --- | --- | --- | --- | --- |
| `DEBUG` | Detailed debug information | `debug` | `debug` | Good fit. WP-CLI already gates this behind `--debug`, which matches the intent well. |
| `INFO` | Interesting events | `log` | `log` | Good fit. This is ordinary informational output and should remain suppressible with `--quiet`. |
| `NOTICE` | Normal but significant events | `warning` + level name | `log` + level name | **Main mismatch.** A notice is not inherently a warning. Mapping it to `warning` changes both the severity and the user-facing prefix. |
| `WARNING` | Exceptional occurrences that are not errors | `warning` + level name | `warning` + level name | Good fit. WP-CLI warning semantics are close to PSR-3 warning semantics. |
| `ERROR` | Runtime errors that should be logged and monitored | `error` + level name + `exit=false` | `error` + level name + `exit=false` | Good fit. This preserves the error channel without forcing process termination. |
| `CRITICAL` | Critical conditions | `error` + level name + `exit=true` | `error` + level name; exit policy is separate | `error` is the closest WP-CLI channel, but the forced exit is a control-flow decision, not a pure level-mapping decision. |
| `ALERT` | Action must be taken immediately | `error` + level name + `exit=true` | `error` + level name; exit policy is separate | Same caveat as `CRITICAL`. WP-CLI has no dedicated alert channel. |
| `EMERGENCY` | System is unusable | `error` + level name + `exit=true` | `error` + level name; exit policy is separate | Same caveat as `CRITICAL`. `error` is the closest available channel. |

## Review of the current strategy

### What aligns well with PSR-3

- `DEBUG -> debug` is a strong match.
- `INFO -> log` is a strong match.
- `WARNING -> warning` is a strong match.
- `ERROR -> error` is a strong match.
- Mapping `CRITICAL`, `ALERT`, and `EMERGENCY` to `error` is reasonable because WP-CLI does not provide higher-severity output methods.
- Including the PSR-3 level name once multiple PSR-3 levels share the same WP-CLI method is useful and should stay.

### What does not align cleanly

- `NOTICE -> warning` does **not** align with the spirit of PSR-3. A notice is “normal but significant”, while `WP_CLI::warning()` is explicitly for warning conditions. The current mapping overstates severity and can make routine-but-notable events look like problems.

### Important ambiguity

- `exit=true` for `CRITICAL`, `ALERT`, and `EMERGENCY` may be a sensible package default for CLI commands, but it is better described as a **handler policy** than as part of the PSR-3-to-WP-CLI level mapping itself.
- In other words: `CRITICAL+ -> error` is the level mapping; “should this log call terminate the command?” is a second decision layered on top.

## Recommended default strategy for future code changes

If the goal is to align the default mapping more closely with PSR-3 while staying idiomatic for WP-CLI, the default should be:

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

If backwards compatibility requires keeping `exit=true` for `CRITICAL` and above, that should be documented as an opinionated default rather than as the only semantically correct mapping.

## Takeaways for issue #15

- The strongest documented mismatch is `NOTICE -> warning`.
- Changing `NOTICE` to `log` is consistent with both PSR-3 semantics and WP-CLI output intent.
- `includeLevelName` should still be kept for `NOTICE` so it remains distinguishable from `INFO`.
- If issue `#15` also revisits `exit=true` for `CRITICAL+`, treat that as a separate design discussion from the `NOTICE` mapping fix.
