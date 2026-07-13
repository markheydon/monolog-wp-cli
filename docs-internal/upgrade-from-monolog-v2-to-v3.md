# Upgrade monolog-wp-cli from Monolog 2 to 3

**Status**

- ✅ Phase 1 completed (13 July 2026)
- 🔄 Phase 2–4 next priority
- ⏳ Phases 5–8 pending

**TL;DR:** Create a new major release (v3.0.0) supporting Monolog 3 with PHP 8.1+ as the floor, while maintaining the v2.x branch on Monolog 2. A `2.x` maintenance branch has been created and `v2.3.0` released as the final planned Monolog 2 feature release. The `main` branch is now the development line for v3.0.0 and beyond. This involves updating the handler to work with Monolog's new `Level` enum and `LogRecord` object, refreshing the CI matrix, and updating all version and compatibility documentation.

---

## Steps

### Phase 1: Branch strategy ✅ Completed (13 July 2026)

- [x] Create a new `2.x` branch from the current codebase to hold the Monolog 2 compatible release line
- [x] Release `v2.3.0` from the `2.x` branch as the final planned Monolog 2 feature release
- [x] Establish `2.x` as the maintenance branch for Monolog 2 users
- [x] Establish `main` as the development branch targeting Monolog 3 support (`v3.0.0`)

---

### Phase 2: Composer & package constraints *(parallel with Phase 3)*  ✅ Completed (13 July 2026)

- [x] Update `composer.json`
  - [x] Change `require.monolog/monolog` from `^2.5` → `^3.0`
  - [x] Change `require.php` from `^7.2 || ^8.0` → `^8.1`
  - [x] Update `require-dev` versions if needed for PHP 8.1 compatibility
- [x] Validate changes using:

```bash
composer validate --strict
```

---

### Phase 3: Core handler implementation *(parallel with Phase 2)* ✅ Completed (13 July 2026)

- [x] Update `src/Monolog/Handler/WPCLIHandler.php`
  - [x] Replace all `Logger::*` level constants with `Level` enum cases
  - [x] Update method signature to use `LogRecord`
  - [x] Convert record array access (`$record['level']`, `$record['context']`) to object property access (`$record->level`, `$record->context`)
  - [x] Ensure level comparisons work correctly with Monolog 3
  - [x] Add required imports:
    - [x] `use Monolog\Level;`
    - [x] `use Monolog\LogRecord;`
- [x] Update `getDefaultLoggerMap()` and related level mapping logic
- [x] Review handler implementation for any remaining Monolog 2 assumptions
- [x] Review any code relying on integer level comparisons

---

### Phase 4: Test implementation *(depends on Phase 3)* ✅ Completed (13 July 2026)

- [x] Update `tests/Monolog/Handler/WPCLIHandlerTest.php`
  - [x] Replace `Logger::*` constants with `Level::*`
  - [x] Update logger instantiation for Monolog 3
  - [x] Update any manually created records to use `LogRecord`
- [x] Review `tests/Monolog/Stubs/MockWPCLI.php`
- [x] Review `tests/Monolog/Stubs/MockExitException.php`
- [x] Migrate any Monolog 2 record arrays to Monolog 3 structures
- [x] Verify `tests/runtime-smoke.php` works against Monolog 3

---

### Phase 5: CI and test configuration *(depends on Phase 4)*

- [x] Update `.github/workflows/php.yml`

  Runtime compatibility matrix:

  - [x] Remove PHP 7.2
  - [x] Remove PHP 7.3
  - [x] Remove PHP 7.4
  - [x] Remove PHP 8.0
  - [x] Keep PHP 8.1
  - [x] Keep PHP 8.2
  - [x] Keep PHP 8.3
  - [x] Keep PHP 8.4
  - [x] Keep PHP 8.5

  Unit test matrix:

  - [x] Remove PHP 7.x jobs
  - [x] Keep PHP 8.1–8.5 jobs

  WordPress smoke coverage:

  - [x] Update matrix to supported WordPress/PHP tuples only

- [x] Run local validation:

```bash
composer test
composer run test:runtime-smoke
```

---

### Phase 6: Documentation *(can run in parallel with Phase 5)*

#### Compatibility & release policy

- [x] Update `docs/explanation/compatibility-and-release-line-policy.md`
  - [x] Add Monolog 3 release line
  - [x] Document PHP 8.1+ requirement

#### WordPress testing documentation

- [x] Update `docs/how-to/test-under-wordpress.md`
  - [x] Refresh supported WordPress/PHP combinations

#### Internal version strategy

- [x] Update `docs-internal/php-version-strategy.md`
  - [x] Add Monolog 3 application snapshot
  - [x] Document PHP 8.1+ runtime requirement

#### Internal WordPress policy

- [x] Update `docs-internal/wordpress-support-policy.md`
  - [x] Refresh supported tuples
  - [x] Refresh PHP support window

#### README

- [x] Update `README.md`
  - [x] State support for Monolog 3.x
  - [x] State PHP 8.1+ requirement
  - [x] Refresh badges if required
  - [x] Review installation examples

#### Upgrade guide

- [x] Create or update `UPGRADE.md`
  - [x] Document Monolog 2 → Monolog 3 migration
  - [x] Document PHP version changes
  - [x] Explain `Level` enum migration
  - [x] Explain `LogRecord` migration
  - [x] Link to Monolog's own upgrade documentation

---

### Phase 7: Validation *(depends on Phase 5 and Phase 6)*

- [ ] Run full test suite

```bash
composer test
composer run test:runtime-smoke
```

- [ ] Verify all CI jobs complete successfully
- [ ] Review workflow results on GitHub

#### Optional manual WordPress smoke test

- [ ] Create local WordPress test environment
- [ ] Run:

```bash
composer run test:wp
```

- [ ] Verify WP-CLI logging output behaves correctly

---

### Phase 8: Git and release *(after all tests pass)*

- [ ] Create pull request for Monolog 3 upgrade branch
- [ ] Complete code review
- [ ] Merge into `main`
- [ ] Tag and publish `v3.0.0`
- [ ] Publish release notes
- [ ] Backport critical fixes to `2.x` when required

---

## Relevant files

### Core implementation

- `src/Monolog/Handler/WPCLIHandler.php` — main handler; adapt to `Level` enum and `LogRecord`
- `composer.json` — package constraints

### Tests

- `tests/Monolog/Handler/WPCLIHandlerTest.php`
- `tests/Monolog/Stubs/MockWPCLI.php`
- `tests/Monolog/Stubs/MockExitException.php`
- `phpunit.xml.dist`

### CI

- `.github/workflows/php.yml`

### Documentation

- `README.md`
- `UPGRADE.md`
- `docs/explanation/compatibility-and-release-line-policy.md`
- `docs/how-to/test-under-wordpress.md`
- `docs-internal/php-version-strategy.md`
- `docs-internal/wordpress-support-policy.md`

---

## Verification

- [ ] Unit tests pass on PHP 8.1–8.5
- [ ] Runtime smoke tests pass
- [ ] `composer validate --strict` passes
- [ ] CI workflow is green
- [ ] WordPress integration test passes
- [ ] Manual WP-CLI logging spot check passes

---

## Decisions & Scope

### Branching

- ✅ `2.x` branch created
- ✅ `v2.3.0` released as the final planned Monolog 2 feature release
- ✅ Monolog 2 maintenance continues on `2.x`
- ✅ Monolog 3 development now targets `main`
- ✅ Repository version remains aligned with Monolog major version

### PHP floor

- PHP 8.1+

### Scope

- Monolog 3 upgrade only
- No unrelated refactoring
- No feature additions
