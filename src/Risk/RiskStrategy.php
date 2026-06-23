<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\VerificationReport;

/**
 * Turns a finished report of hard facts into an advisory {@see RiskAssessment}.
 *
 * This is the seam: {@see RuleBasedRiskStrategy} is the deterministic default, an
 * application may provide a fully custom implementation, and the optional AI package
 * ships an LLM-backed strategy — all interchangeable behind this interface.
 */
interface RiskStrategy
{
    public function assess(Counterparty $counterparty, VerificationReport $report): RiskAssessment;
}
