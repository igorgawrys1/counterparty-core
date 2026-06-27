# Counterparty Core

[![Packagist Version](https://img.shields.io/packagist/v/gawrys/counterparty-core.svg)](https://packagist.org/packages/gawrys/counterparty-core)
[![Total Downloads](https://img.shields.io/packagist/dt/gawrys/counterparty-core.svg)](https://packagist.org/packages/gawrys/counterparty-core)
[![CI](https://github.com/igorgawrys1/counterparty-core/actions/workflows/ci.yml/badge.svg)](https://github.com/igorgawrys1/counterparty-core/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/php-8.2%20|%208.3%20|%208.4-777bb4.svg)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-max-brightgreen.svg)](https://phpstan.org/)
[![Psalm](https://img.shields.io/badge/Psalm-level%201-brightgreen.svg)](https://psalm.dev/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

The framework-agnostic heart of the **Counterparty Verification** toolkit - per-country
**registry lookups**, **sanctions screening** and a pluggable **risk engine** for
counterparty due diligence. Hexagonal: it depends only on PSR interfaces, so it drops into
any framework (or none).

```php
$outcome = $verifier->verify(new Counterparty('Acme Sp. z o.o.', 'PL', nip: '1234567890'));

$outcome->report->worstStatus();    // hard facts (deterministic)
$outcome->assessment->level;        // advisory RiskLevel
$outcome->requiresHumanReview();    // true on adverse / inconclusive / low-confidence
```

> ⚠️ **This is a due-diligence aid, not a compliance product.** It does **not** make you
> "AML compliant" - that remains your responsibility. The risk score is **advisory**.

## Features

- **Hexagonal & dependency-light** - PSR-18/17 (HTTP), PSR-16 (cache), PSR-3 (log), PSR-20
  (clock). No framework, no vendor SDK in the core.
- **Capability-aware registries** - drivers declare a `RegistryCapability`; the verifier
  routes by *(country + capability)* and returns an honest `inconclusive` when nothing
  covers a request. Adding a country is one driver + one registration.
- **Reference adapters (PSR-18)** - PL White List (VAT status, IBAN match, search-id proof),
  EU VIES, KRS, CEIDG, REGON, CRBR, sanctions.network (default) and OpenSanctions.
- **Pluggable risk engine** - `RuleBasedRiskStrategy` composed of small `RiskRule`s; add
  your own without subclassing, or implement `RiskStrategy` for a bespoke model.
- **Contract tests as a feature** - `RegistryDriverContractTestCase` certifies any
  third-party driver.
- **Strict** - PHPStan max + Psalm level 1, fully typed.

## Installation

```bash
composer require gawrys/counterparty-core
```

This package is HTTP-client agnostic: it depends on the PSR-18 / PSR-17 **interfaces** and
declares `psr/http-client-implementation` + `psr/http-factory-implementation` as virtual
requirements, so Composer asks you to choose a client. Install any PSR-18 implementation, e.g.:

```bash
composer require symfony/http-client nyholm/psr7
# or a Guzzle PSR-18 adapter, etc.
```

## Usage

### Quick start (auto-wired)

The factory assembles the bundled checks (White List, VIES, sanctions.network) + the
rule-based strategy. It auto-discovers your installed PSR-18 client and PSR-17 factories:

```php
use Gawrys\Counterparty\CounterpartyVerifierFactory;
use Gawrys\Counterparty\Counterparty;

$verifier = CounterpartyVerifierFactory::discover();           // or ::create($psr18Client)
$outcome  = $verifier->verify(new Counterparty('Acme', 'PL', nip: '1234567890', euVat: 'PL1234567890'));
```

### Manual wiring (full control)

The clock is optional and defaults to `SystemClock`; pass a `FrozenClock` in tests.

```php
use Gawrys\Counterparty\Verifier;
use Gawrys\Counterparty\Risk\RuleBasedRiskStrategy;
use Gawrys\Counterparty\Check\WhiteListCheck;
use Gawrys\Counterparty\Adapter\WhiteList\HttpWhiteListClient;
use Gawrys\Counterparty\Http\JsonHttpClient;

$http = new JsonHttpClient($psr18Client, $psr17Factory, $psr17Factory);

$verifier = new Verifier(
    checks: [new WhiteListCheck(new HttpWhiteListClient($http))], // clock defaults to SystemClock
    riskStrategy: RuleBasedRiskStrategy::withDefaultRules(),
);
```

### Add a country (one driver + one registration)

```php
final readonly class GermanRegistryDriver extends AbstractRegistryDriver
{
    public function capabilities(): array { return [RegistryCapability::LegalEntityData]; }
    public function countries(): array    { return ['DE']; }
    public function lookup(LookupRequest $request): LookupResult { /* ... */ }
}

$registries->extend('de', fn () => new GermanRegistryDriver(/* ... */));
```

### Add a custom scoring rule

```php
final class HighRiskCountryRule implements RiskRule
{
    public function evaluate(RiskContext $context): iterable
    {
        if (in_array($context->counterparty->country, ['XX', 'YY'], true)) {
            yield new RiskSignal('geo.high_risk', 0.6, adverse: false);
        }
    }
}

$strategy = new RuleBasedRiskStrategy([new HighRiskCountryRule(), /* ...defaults */]);
```

### Certify a third-party driver

```php
final class GermanRegistryDriverTest extends RegistryDriverContractTestCase
{
    protected function createDriver(): RegistryDriver { /* wire to a mocked HTTP client */ }
    protected function supportedRequest(): LookupRequest { /* a request it supports */ }
}
```

Full guides live in the **[documentation](https://igorgawrys1.github.io/counterparty-verification/)**.

## Part of the toolkit

| Package | Purpose |
| --- | --- |
| **counterparty-core** (this) | Domain, registries, risk engine, PSR-18 adapters |
| [counterparty-ai](https://github.com/igorgawrys1/counterparty-ai) | Optional advisory AI risk research |
| [counterparty-laravel](https://github.com/igorgawrys1/counterparty-laravel) | Laravel bridge |
| [counterparty-bundle](https://github.com/igorgawrys1/counterparty-bundle) | Symfony bundle |

## Testing

```bash
composer check   # php-cs-fixer + PHPStan max + Psalm level 1 + PHPUnit
composer test    # PHPUnit only
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Contributing & Security

Pull requests welcome. Please report security issues privately - see [SECURITY.md](SECURITY.md).

## Credits

- [Igor Gawrys](https://github.com/igorgawrys1)

## License

The MIT License (MIT). See [LICENSE](LICENSE).
