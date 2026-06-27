<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk\Rule;

use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Risk\Evidence;
use Gawrys\Counterparty\Risk\RiskContext;
use Gawrys\Counterparty\Risk\RiskRule;
use Gawrys\Counterparty\Risk\RiskSignal;

/**
 * Raises a low, non-adverse signal for every inconclusive check so that gaps in
 * coverage surface as "needs human review" rather than being silently treated as a pass.
 */
final class InconclusiveCoverageRule implements RiskRule
{
    public function evaluate(RiskContext $context): iterable
    {
        foreach ($context->report as $result) {
            if ($result->status === CheckStatus::Inconclusive) {
                yield new RiskSignal(
                    'coverage.inconclusive',
                    0.2,
                    false,
                    Evidence::ungrounded(\sprintf('%s: %s', $result->checkName, $result->summary), 0.0),
                );
            }
        }
    }
}
