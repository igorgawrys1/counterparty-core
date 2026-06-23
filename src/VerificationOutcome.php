<?php

declare(strict_types=1);

namespace Gawrys\Counterparty;

use Gawrys\Counterparty\Report\VerificationReport;
use Gawrys\Counterparty\Risk\RiskAssessment;

/**
 * The complete result of {@see Verifier::verify()}: the counterparty, the hard-fact
 * report, and the advisory risk assessment derived from it.
 */
final readonly class VerificationOutcome
{
    public function __construct(
        public Counterparty $counterparty,
        public VerificationReport $report,
        public RiskAssessment $assessment,
    ) {
    }

    public function requiresHumanReview(): bool
    {
        return $this->assessment->requiresHumanReview();
    }

    public function hasAdverseFindings(): bool
    {
        return $this->report->hasAdverseFindings();
    }
}
