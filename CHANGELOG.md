# Changelog

All notable changes are documented here, following [Keep a Changelog](https://keepachangelog.com/)
and [Semantic Versioning](https://semver.org/).

## [0.1.4]

### Added
- Automated release pipeline: pushing a `v*` tag creates a GitHub Release from the changelog
  and notifies Packagist.

## [0.1.3]

### Changed
- Maintainer contact e-mail updated to igor@gawrys.me (composer `authors`, SECURITY.md).

## [0.1.1]

### Added
- `CounterpartyVerifierFactory::create()` / `::discover()` - assemble a ready-to-use verifier
  with the bundled checks + rule-based strategy (auto-discovering PSR-18/17 via php-http/discovery).
- Declared virtual `psr/http-client-implementation` + `psr/http-factory-implementation`
  requirements so Composer guides consumers to install an HTTP client.

### Changed
- The clock argument is now optional on `Verifier`, `WhiteListCheck`, `ViesCheck` and
  `SanctionsScreeningCheck`, defaulting to `SystemClock` (backward compatible).

## [0.1.0]

### Added
- Hexagonal domain: `Counterparty`, `CheckResult`, `VerificationReport`, `RiskAssessment`,
  `Evidence`; `CheckStatus` / `RiskLevel` / `RegistryCapability` enums.
- `Verifier` orchestrator; pluggable `RuleBasedRiskStrategy` composed of `RiskRule`s.
- Shared `AbstractDriverManager`; capability-aware `RegistryManager`; `SanctionsManager`.
- Reference PSR-18 adapters: PL White List, EU VIES, KRS, CEIDG, REGON, CRBR,
  sanctions.network (default) and OpenSanctions (commercial-licence note).
- `RegistryDriverContractTestCase` for certifying third-party drivers.
