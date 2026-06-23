# gawrys/counterparty-core

Framework-agnostic core for counterparty due diligence. Depends only on PSR interfaces
(PSR-18/17 HTTP, PSR-16 cache, PSR-3 log, PSR-20 clock).

> ⚠️ A **due-diligence aid**, not a guarantee of AML compliance. Risk output is advisory.

## Install

```bash
composer require gawrys/counterparty-core
```

## What's inside

- **Domain:** `Counterparty`, `CheckResult`, `VerificationReport`, `RiskAssessment`,
  `Evidence`; enums `CheckStatus`, `RiskLevel`, `RegistryCapability`.
- **Orchestration:** `Verifier` runs `Check`s → `VerificationReport` → `RiskStrategy` →
  `RiskAssessment` (returned together as a `VerificationOutcome`).
- **Pluggable scoring:** `RuleBasedRiskStrategy` composed from `RiskRule`s.
- **Driver manager:** generic `AbstractDriverManager` with `extend()` and `DriverFactory`
  paths; `RegistryManager` (capability-routed) and `SanctionsManager`.
- **Reference adapters (PSR-18 only):** PL White List, EU/VIES, KRS, CEIDG, REGON, CRBR,
  sanctions.network (default) and OpenSanctions (commercial-licence note).
- **Contract tests:** `RegistryDriverContractTestCase` — extend it to certify your driver.

## Add a country in three steps

```php
final readonly class GermanRegistryDriver extends AbstractRegistryDriver
{
    public function capabilities(): array { return [RegistryCapability::LegalEntityData]; }
    public function countries(): array { return ['DE']; }
    public function lookup(LookupRequest $r): LookupResult { /* ... */ }
}

$registries->extend('de', fn () => new GermanRegistryDriver(/* ... */));
// The verifier now routes LegalEntityData for DE here. No core changes.
```

## Certify a driver

```php
final class GermanRegistryDriverTest extends RegistryDriverContractTestCase
{
    protected function createDriver(): RegistryDriver { /* wire to a mocked HTTP client */ }
    protected function supportedRequest(): LookupRequest { /* a request it supports */ }
}
```

MIT licensed.
