<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Report;

use Gawrys\Counterparty\Enum\CheckStatus;

/**
 * The deterministic outcome of a single {@see \Gawrys\Counterparty\Check\Check}.
 *
 * Hard facts only — never an opinion. The optional {@see self::$proofId} carries a
 * due-diligence proof token (e.g. the PL White List search identifier) together with the
 * timestamp so the lookup can be evidenced later. The raw payload retains the provider's
 * own response for audit; adapters are responsible for not placing unnecessary PII here.
 */
final readonly class CheckResult
{
    /**
     * @param array<string, mixed> $raw provider's raw, structured response payload
     */
    public function __construct(
        public CheckStatus $status,
        public string $source,
        public string $checkName,
        public string $summary,
        public \DateTimeImmutable $completedAt,
        public array $raw = [],
        public ?string $proofId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function pass(
        string $source,
        string $checkName,
        string $summary,
        \DateTimeImmutable $completedAt,
        array $raw = [],
        ?string $proofId = null,
    ): self {
        return new self(CheckStatus::Pass, $source, $checkName, $summary, $completedAt, $raw, $proofId);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fail(
        string $source,
        string $checkName,
        string $summary,
        \DateTimeImmutable $completedAt,
        array $raw = [],
        ?string $proofId = null,
    ): self {
        return new self(CheckStatus::Fail, $source, $checkName, $summary, $completedAt, $raw, $proofId);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function warning(
        string $source,
        string $checkName,
        string $summary,
        \DateTimeImmutable $completedAt,
        array $raw = [],
        ?string $proofId = null,
    ): self {
        return new self(CheckStatus::Warning, $source, $checkName, $summary, $completedAt, $raw, $proofId);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function inconclusive(
        string $source,
        string $checkName,
        string $reason,
        \DateTimeImmutable $completedAt,
        array $raw = [],
    ): self {
        return new self(CheckStatus::Inconclusive, $source, $checkName, $reason, $completedAt, $raw, null);
    }

    public function isAdverse(): bool
    {
        return $this->status->isAdverse();
    }
}
