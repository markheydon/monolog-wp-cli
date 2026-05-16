# Reference: WPCLIHandler

`MHCG\Monolog\Handler\WPCLIHandler` is a Monolog handler that forwards log records to WP-CLI output methods.

## Runtime constraints

- Intended for WP-CLI execution.
- Constructor throws `RuntimeException` when WP-CLI runtime is not detected.

## Constructor

```php
new WPCLIHandler($level = Logger::WARNING, $bubble = true, $verbose = false)
```

- `$level`: minimum logging level for handler triggering.
- `$bubble`: whether records continue to other handlers.
- `$verbose`: enables verbose formatter output (also enabled by `WP_DEBUG`).

## Default formatter behaviour

- Standard format: `%message%`
- Verbose format: `%message% %context% %extra%`

Verbose output is enabled when either:

- `WP_DEBUG` is enabled, or
- constructor `$verbose` is `true`

## Default Monolog-to-WP-CLI mapping

| Monolog level | WP-CLI method | includeLevelName | exit |
| --- | --- | --- | --- |
| DEBUG | `debug` | no | n/a |
| INFO | `log` | no | n/a |
| NOTICE | `warning` | yes | n/a |
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
- `validateLoggerMap(array $map, int $level, string $levelName = '')`