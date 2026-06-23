# Contributing

Thanks for your interest in improving Counterparty Verification.

## Development

This is a development **monorepo**; the four packages are split to read-only repositories
for distribution. Work against the monorepo.

```bash
composer install
composer check     # php-cs-fixer (dry-run) + PHPStan max + Psalm level 1 + PHPUnit
composer cs:fix    # apply the coding standard
```

## Ground rules

- **Keep `composer check` green.** PHPStan runs at level max (with larastan / phpstan-symfony
  for the bridges); Psalm at errorLevel 1.
- **Never weaken a static-analysis baseline to make an error go away** — fix the code.
- **Add tests** for new behaviour. Domain logic is covered by unit tests; external APIs are
  mocked (no live network in CI). New registry drivers should pass
  `RegistryDriverContractTestCase`.
- **English** for all code, comments, docblocks and commit messages.
- **Conventional commits** (`feat:`, `fix:`, `docs:`, `chore:` …).
- Target **PHP 8.2+**; strict types everywhere.

## Architecture in one line

The `core` package depends only on PSR interfaces. Adapters and framework bridges live
behind ports; adding a country/registry/provider is a new driver plus a registration, with
no changes to the core. See the [README](README.md) for the full picture.

## AI subsystem

The `ai` package is advisory only and must never decide hard pass/fail. Every model claim
must be grounded in a tool's source URL; prompts are versioned (bump `RiskPromptBuilder::VERSION`
on change).
