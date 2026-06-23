# Changelog

All notable changes are documented here, following [Keep a Changelog](https://keepachangelog.com/)
and [Semantic Versioning](https://semver.org/).

## [0.1.0]

### Added
- Hexagonal domain: `Counterparty`, `CheckResult`, `VerificationReport`, `RiskAssessment`,
  `Evidence`; `CheckStatus` / `RiskLevel` / `RegistryCapability` enums.
- `Verifier` orchestrator; pluggable `RuleBasedRiskStrategy` composed of `RiskRule`s.
- Shared `AbstractDriverManager`; capability-aware `RegistryManager`; `SanctionsManager`.
- Reference PSR-18 adapters: PL White List, EU VIES, KRS, CEIDG, REGON, CRBR,
  sanctions.network (default) and OpenSanctions (commercial-licence note).
- `RegistryDriverContractTestCase` for certifying third-party drivers.
