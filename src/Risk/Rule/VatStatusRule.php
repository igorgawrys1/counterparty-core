<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk\Rule;

use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Risk\Evidence;
use Gawrys\Counterparty\Risk\RiskContext;
use Gawrys\Counterparty\Risk\RiskRule;
use Gawrys\Counterparty\Risk\RiskSignal;

/**
 * Flags an inactive or unconfirmed VAT status from the White List or VIES.
 */
final class VatStatusRule implements RiskRule
{
    public function evaluate(RiskContext $context): iterable
    {
        foreach ([Source::WHITE_LIST, Source::VIES] as $source) {
            foreach ($context->report->fromSource($source) as $result) {
                if ($result->status === CheckStatus::Fail) {
                    yield new RiskSignal(
                        'vat.inactive',
                        0.6,
                        false,
                        Evidence::ungrounded($result->summary, 1.0),
                    );
                }
            }
        }
    }
}
