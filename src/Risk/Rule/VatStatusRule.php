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
    private const SOURCE_URLS = [
        Source::WHITE_LIST => 'https://www.podatki.gov.pl/wykaz-podatnikow-vat-wyszukiwarka/',
        Source::VIES => 'https://ec.europa.eu/taxation_customs/vies/',
    ];

    public function evaluate(RiskContext $context): iterable
    {
        foreach (self::SOURCE_URLS as $source => $url) {
            foreach ($context->report->fromSource($source) as $result) {
                if ($result->status === CheckStatus::Fail) {
                    yield new RiskSignal(
                        'vat.inactive',
                        0.6,
                        false,
                        Evidence::grounded($result->summary, $url, 0.9),
                    );
                }
            }
        }
    }
}
