<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk\Rule;

use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Risk\Evidence;
use Gawrys\Counterparty\Risk\RiskContext;
use Gawrys\Counterparty\Risk\RiskRule;
use Gawrys\Counterparty\Risk\RiskSignal;

/**
 * Escalates any adverse sanctions-screening result to the maximum, adverse score.
 */
final class SanctionsHitRule implements RiskRule
{
    public function evaluate(RiskContext $context): iterable
    {
        foreach ($context->report->fromSource(Source::SANCTIONS) as $result) {
            if ($result->isAdverse()) {
                yield new RiskSignal(
                    'sanctions.hit',
                    1.0,
                    true,
                    Evidence::ungrounded($result->summary, 1.0),
                );
            }
        }
    }
}
