# Upgrade monolog-wp-cli from Monolog v2 to v3 (Planned Update)

**TL;DR:** Create a new major release (v3.0.0) supporting Monolog 3 with PHP 8.1+ as the floor, while maintaining the v2.x branch on Monolog 2. Create a `2.x` maintenance branch from the current v2.2.1 codebase. The `main` branch then becomes the development line for v3.0.0 and beyond. This involves updating the handler to work with Monolog's new `Level` enum and `LogRecord` object, refreshing the CI matrix, and updating all version/compatibility documentation.

---

## Steps

### Phase 1: Branch strategy
1. Create a new branch `2.x` from the current `main` (v2.2.1) to hold the Monolog v2 compatible codebase
   - The `2.x` branch becomes the maintenance release line, preserving Monolog 2 support and receiving patches (v2.2.2, v2.3.0, etc.)
   - `main` then becomes the development branch for Monolog 3 support, targeting v3.0.0 release

### Phase 2: Composer & package constraints (*parallel with Phase 3*)
2. Update [composer.json](composer.json):
   - Change `require.monolog/monolog` from `^2.5` → `^3.0`
   - Change `require.php` from `^7.2 || ^8.0` → `^8.1`
   - Update `require-dev` versions if needed for PHP 8.1 compatibility
3. Validate changes: `composer validate --strict`

### Phase 3: Core handler implementation (*parallel with Phase 2*)
4. Update [src/Monolog/Handler/WPCLIHandler.php](src/Monolog/Handler/WPCLIHandler.php):
   - Replace all `Logger::WARNING`, `Logger::DEBUG` constants with `Level` enum cases (e.g., `Level::Warning`)
   - Update method signature: `isHandling(LogRecord $record): bool` instead of `array $record`
   - Convert array access (`$record['level']`, `$record['context']`) to object properties (`$record->level`, `$record->context`)
   - Ensure level comparisons work with `Level` enum values (`.value` property if needing integer)
   - Add `use Monolog\Level; use Monolog\LogRecord;` imports
5. Update method `getDefaultLoggerMap()` and related: ensure all level keys use `Level` enum integers
6. Review [src/Monolog/Handler/WPCLIHandler.php](src/Monolog/Handler/WPCLIHandler.php) logic carefully — any code checking `$record['level']` or storing integer comparisons must adapt to enum

### Phase 4: Test implementation (*depends on Phase 3*)
7. Update [tests/Monolog/Handler/WPCLIHandlerTest.php](tests/Monolog/Handler/WPCLIHandlerTest.php):
   - Replace `Logger::DEBUG` constants in test setup with `Level::Debug` enum
   - Update logger instantiation and method calls to use Monolog 3 API
   - Adapt any mock record creation to use `LogRecord` object or Monolog 3's record factory
8. Update [tests/Monolog/Stubs/MockWPCLI.php](tests/Monolog/Stubs/MockWPCLI.php) and [tests/Monolog/Stubs/MockExitException.php](tests/Monolog/Stubs/MockExitException.php) if they construct record arrays — migrate to `LogRecord` format
9. Verify `tests/runtime-smoke.php` works with v3 (run locally first)

### Phase 5: CI and test configuration (*depends on Phase 4*)
10. Update [.github/workflows/php.yml](.github/workflows/php.yml):
    - Remove PHP 7.2, 7.3, 7.4, 8.0 from `runtime-compat` matrix
    - Keep PHP 8.1, 8.2, 8.3, 8.4, 8.5 in `runtime-compat`
    - Update `unit-tests` matrix: remove PHP 7.x jobs, keep 8.1–8.5
    - Update `WordPress` smoke job: filter to only supported tuples (WordPress 7.0 on PHP 8.4+, WordPress 6.8 on PHP 8.3–8.4)
11. Run CI locally first: `composer test` and `composer run test:runtime-smoke` on PHP 8.1+

### Phase 6: Documentation (*can run in parallel with Phase 5*)
12. Update [docs/explanation/compatibility-and-release-line-policy.md](docs/explanation/compatibility-and-release-line-policy.md):
    - Add new section for Monolog 3 line
    - State: "Version 3.0.0+ supports Monolog 3.x and requires PHP 8.1+"
13. Update [docs/how-to/test-under-wordpress.md](docs/how-to/test-under-wordpress.md):
    - Update supported WordPress/PHP tuples to match new policy snapshot
14. Update [docs-internal/php-version-strategy.md](docs-internal/php-version-strategy.md):
    - Add new "Current application snapshot" for v3.x: active line = Monolog `^3.0`, chosen path = alignment, runtime = PHP 8.1+
15. Update [docs-internal/wordpress-support-policy.md](docs-internal/wordpress-support-policy.md):
    - Refresh "Current application snapshot (date)" with new tuples and eligible PHP window (8.3–8.5 per upstream support)
16. Update [README.md](README.md):
    - Clarify: "Supports Monolog 3.x and requires PHP 8.1+"
    - Update installation example if needed
    - Update any badges showing supported versions
17. Create or update [UPGRADE.md](UPGRADE.md) if not present:
    - Document upgrade path: Monolog v2 → v3, PHP 7.2 → 8.1
    - Highlight Level enum changes and LogRecord object usage
    - Link to Monolog's own UPGRADE.md for comprehensive reference

### Phase 7: Validation (*depends on Phase 5, 6*)
18. Run full local test suite:
    - `composer test` (unit tests, PHP 8.1+)
    - `composer run test:runtime-smoke` (runtime check)
19. Verify CI: all workflow jobs pass on the release branch
20. Manual WordPress smoke test (if time):
    - Set up local WordPress environment with [tests/wordpress/docker-compose.yml](tests/wordpress/docker-compose.yml)
    - Run `composer run test:wp` and verify logs output correctly via WP-CLI

### Phase 8: Git and release (*after all tests pass*)
21. Create a pull request from the Monolog 3 upgrade branch into `main`
22. Merge and tag as version `3.0.0` with release notes
23. Backport critical fixes to `2.x` maintenance branch as needed

---

## Relevant files

- [src/Monolog/Handler/WPCLIHandler.php](src/Monolog/Handler/WPCLIHandler.php) — main handler; adapt to Level enum and LogRecord object
- [tests/Monolog/Handler/WPCLIHandlerTest.php](tests/Monolog/Handler/WPCLIHandlerTest.php) — unit tests; update level constants and record mocking
- [tests/Monolog/Stubs/MockWPCLI.php](tests/Monolog/Stubs/MockWPCLI.php), [tests/Monolog/Stubs/MockExitException.php](tests/Monolog/Stubs/MockExitException.php) — test fixtures
- [composer.json](composer.json) — Monolog and PHP constraints
- [.github/workflows/php.yml](.github/workflows/php.yml) — CI matrix
- [phpunit.xml.dist](phpunit.xml.dist) — test config (if needed for PHP 8.1)
- [docs/explanation/compatibility-and-release-line-policy.md](docs/explanation/compatibility-and-release-line-policy.md) — public Diataxis explanation doc
- [docs/how-to/test-under-wordpress.md](docs/how-to/test-under-wordpress.md) — WordPress test how-to
- [docs-internal/php-version-strategy.md](docs-internal/php-version-strategy.md) — version policy
- [docs-internal/wordpress-support-policy.md](docs-internal/wordpress-support-policy.md) — WordPress/PHP support tuples
- [README.md](README.md) — public compatibility claims
- [UPGRADE.md](UPGRADE.md) — create or update with v2→v3 migration guidance

---

## Verification

1. **Unit tests pass** — `composer test` succeeds on PHP 8.1–8.5
2. **Runtime smoke passes** — `composer run test:runtime-smoke` succeeds on each PHP version
3. **Composer valid** — `composer validate --strict` exits cleanly
4. **CI workflow green** — All jobs in `.github/workflows/php.yml` pass on the release branch
5. **WordPress integration** — `composer run test:wp` completes without errors (local or CI)
6. **Manual spot check** — Create a simple WP-CLI command with WPCLIHandler, verify logging output and levels match expectations

---

## Decisions & scope

- **Branching**: Create `2.x` branch from current main (v2.2.1) for Monolog 2 maintenance. Upgrade work happens on a feature branch merged to `main` as v3.0.0 (aligns with repository version policy: version number matches Monolog major version)
- **PHP floor**: 8.1+ (matches Monolog 3 policy, documented in version strategy)
- **Scope**: Monolog 3 upgrade only; no unrelated refactors or feature additions
- **v2.x branch**: Maintenance line for Monolog 2 users; critical fixes and patches only, no new features

---

## Further Considerations

1. **Branch timing** — When should the `2.x` branch be created? 
   - *Recommendation*: Create `2.x` immediately from current main before any Monolog 3 work begins. This protects the v2.2.1 release point and allows feature branch work to proceed uninterrupted.

2. **2.x branch policy** — What is the maintenance window for the `2.x` branch?
   - *Recommendation*: The `2.x` branch enters maintenance mode once v3.0.0 is released on `main`. Accept critical security and bug-fix backports; no new feature development.

3. **CI workflow strategy** — Should CI differ per branch (2.x tests PHP 7.2–8.0 + Monolog 2; main tests PHP 8.1+ + Monolog 3)?
   - *Recommendation*: Each branch has its own CI matrix via branch-scoped workflow or conditional jobs; simplest if each branch is independent.
