# AGENTS.md

## Cursor Cloud specific instructions

This repository is a **PHP library** (`dllobell/nanoid`) — there is no web server, database, or long-running application. Development is CLI-only: edit `src/`, then run quality checks.

### Runtime requirements

- **PHP 8.4+** (see `composer.json`). Ubuntu 24.04 default repos only ship PHP 8.3; use the [ondrej/php PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php) for PHP 8.4.
- **Composer** for dependency management.

### Dependency refresh

On VM startup, run `composer install` from the repo root (see update script). No other bootstrap scripts exist.

### Commands

All commands run from the repository root. See also `.github/CONTRIBUTING.md` and `.github/workflows/ci.yml`.

| Task | Command |
|------|---------|
| Install deps | `composer install` |
| Lint (ECS) | `composer lint` |
| Lint fix | `composer lint-fix` |
| Static analysis | `composer analyse` |
| Tests | `composer test` |
| Refactor (optional) | `composer refactor` |

There is no `composer build` or dev server. CI runs `composer install`, then `lint`, `analyse`, and `test`.

### Verifying the library works

Quick smoke test without running the full suite:

```bash
php -r "require 'vendor/autoload.php'; echo Dllobell\NanoId\NanoIdGenerator::create()->generate(), PHP_EOL;"
```

### Gotchas

- **No `composer.lock` in the repo** — `composer install` resolves and may write a lock file locally; that is expected for library development.
- **No environment variables or ports** — nothing to configure beyond PHP and Composer.
- **Pest runs tests in parallel** — `composer test` uses `--parallel`; no extra services required.
