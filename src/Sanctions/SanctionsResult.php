<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Sanctions;

/**
 * The outcome of a sanctions screening: zero or more matches plus provenance.
 */
final readonly class SanctionsResult
{
    /** @var list<SanctionsMatch> */
    public array $matches;

    /**
     * @param iterable<SanctionsMatch> $matches
     */
    public function __construct(
        iterable $matches = [],
        public ?string $sourceUrl = null,
        public ?string $proofId = null,
    ) {
        $this->matches = \is_array($matches) ? array_values($matches) : iterator_to_array($matches, false);
    }

    public static function clear(?string $sourceUrl = null, ?string $proofId = null): self
    {
        return new self([], $sourceUrl, $proofId);
    }

    public function hasMatches(): bool
    {
        return $this->matches !== [];
    }

    public function highestScore(): float
    {
        $score = 0.0;
        foreach ($this->matches as $match) {
            $score = max($score, $match->score);
        }

        return $score;
    }
}
