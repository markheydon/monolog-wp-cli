# Reference: WPCLIHandler

`MHCG\Monolog\Handler\WPCLIHandler` is a Monolog handler that forwards log records to WP-CLI output methods.

## Runtime constraints

- Intended for WP-CLI execution.
- Constructor throws `RuntimeException` when WP-CLI runtime is not detected.

## Constructor

```php
new WPCLIHandler($level = Logger::WARNING, $bubble = true, $verbose = false, ?array $loggerMap = null)
```

- `$level`: minimum logging level for handler triggering.
- `$bubble`: whether records continue to other handlers.
- `$verbose`: enables verbose formatter output (also enabled by `WP_DEBUG`).
- `$loggerMap`: optional per-level overrides merged over the default logger map.

When supplied, `$loggerMap` is merged over `getDefaultLoggerMap()` so callers only need to provide the levels they want to change. Invalid overrides are rejected in the constructor.

Each map entry uses this shape:

```php
[
	Logger::NOTICE => [
		'method' => 'log',
		'includeLevelName' => true,
	],
	Logger::ERROR => [
		'method' => 'error',
		'includeLevelName' => true,
		'exit' => false,
	],
]
```

## Default formatter behaviour

- Standard format: `%message%`
- Verbose format: `%message% %context% %extra%`

That means Monolog `context` and `extra` data are not shown in standard output. They are only rendered when verbose output is enabled.

Verbose output is enabled when either:

- `WP_DEBUG` is enabled, or
- constructor `$verbose` is `true`

Example verbose output from the WordPress integration smoke fixture:

```text
(NOTICE) context from wp {"foo":"bar"} []
```

In this output shape, the trailing `[]` is Monolog `extra` data when no extra keys are present.

## Default Monolog-to-WP-CLI mapping

| Monolog level | WP-CLI method | includeLevelName | exit |
| --- | --- | --- | --- |
| DEBUG | `debug` | no | n/a |
| INFO | `log` | no | n/a |
| NOTICE | `log` | yes | n/a |
| WARNING | `warning` | yes | n/a |
| ERROR | `error` | yes | false |
| CRITICAL | `error` | yes | true |
| ALERT | `error` | yes | true |
| EMERGENCY | `error` | yes | true |

## Handling behaviour

- `DEBUG` is always considered handleable by `isHandling`; WP-CLI decides visibility through `--debug`.
- Unsupported or invalid mapped levels are rejected by logger-map validation.
- `exit=true` is only valid when mapped method is `error`.

## Public static methods

- `getDefaultLoggerMap(): array`
- `getSupportedLevels(array $map): array`
- `validateAllLoggerMapEntries(array $map): void`
- `validateLoggerMap(array $map, int $level, string $levelName = '')`