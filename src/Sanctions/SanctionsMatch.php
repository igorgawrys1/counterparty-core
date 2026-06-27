<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Sanctions;

/**
 * A single potential sanctions-list match returned by a {@see SanctionsProvider}.
 */
final readonly class SanctionsMatch
{
    /**
     * @param float $score match confidence in [0.0, 1.0]
     * @param array<array-key, mixed> $raw provider's raw entry
     */
    public function __construct(
        public string $name,
        public float $score,
        public ?string $listName = null,
        public ?string $sourceUrl = null,
        public array $raw = [],
    ) {
    }
}
