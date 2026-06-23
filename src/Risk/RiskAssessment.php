<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

use Gawrys\Counterparty\Enum\RiskLevel;

/**
 * Advisory output of a {@see RiskStrategy}.
 *
 * This is guidance, NOT a compliance verdict. Hard pass/fail facts live in the
 * {@see \Gawrys\Counterparty\Report\VerificationReport}; this object only contextualises
 * them. When {@see self::requiresHumanReview()} is true the result must not be acted on
 * automatically.
 */
final readonly class RiskAssessment
{
    /** @var list<Evidence> */
    public array $evidence;

    /**
     * @param float $score normalised risk score in [0.0, 1.0]
     * @param iterable<Evidence> $evidence
     */
    public function __construct(
        public RiskLevel $level,
        public float $score,
        public string $summary,
        private bool $humanReviewRequired,
        iterable $evidence = [],
    ) {
        $this->evidence = \is_array($evidence) ? array_values($evidence) : iterator_to_array($evidence, false);
    }

    public function requiresHumanReview(): bool
    {
        return $this->humanReviewRequired;
    }

    /**
     * @return list<Evidence>
     */
    public function groundedEvidence(): array
    {
        return array_values(array_filter(
            $this->evidence,
            static fn (Evidence $e): bool => $e->isGrounded(),
        ));
    }
}
