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
                /** @var mixed $sourceUrl */
                $sourceUrl = $result->raw['sourceUrl'] ?? null;

                yield new RiskSignal(
                    'sanctions.hit',
                    1.0,
                    true,
                    // The deterministic CheckResult is the proof; attach the provider's URL
                    // when available so the evidence is grounded rather than a bare claim.
                    \is_string($sourceUrl) && $sourceUrl !== ''
                        ? Evidence::grounded($result->summary, $sourceUrl, 0.95)
                        : Evidence::ungrounded($result->summary, 0.0),
                );
            }
        }
    }
}
