<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\VerificationReport;

/**
 * Read-only input handed to every {@see RiskRule}: the counterparty plus the finished
 * report of hard facts.
 */
final readonly class RiskContext
{
    public function __construct(
        public Counterparty $counterparty,
        public VerificationReport $report,
    ) {
    }
}
