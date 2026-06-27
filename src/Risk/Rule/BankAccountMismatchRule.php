<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk\Rule;

use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Risk\Evidence;
use Gawrys\Counterparty\Risk\RiskContext;
use Gawrys\Counterparty\Risk\RiskRule;
use Gawrys\Counterparty\Risk\RiskSignal;

/**
 * Flags an IBAN that is not assigned to the counterparty on the PL White List - a
 * common indicator of payment-redirection fraud.
 */
final class BankAccountMismatchRule implements RiskRule
{
    public function evaluate(RiskContext $context): iterable
    {
        foreach ($context->report->fromSource(Source::WHITE_LIST) as $result) {
            if (($result->raw['bankAccountAssigned'] ?? null) === false) {
                yield new RiskSignal(
                    'bank_account.unassigned',
                    0.7,
                    true,
                    Evidence::grounded(
                        'The provided IBAN is not assigned to the counterparty on the White List.',
                        'https://www.podatki.gov.pl/wykaz-podatnikow-vat-wyszukiwarka/',
                        0.9,
                    ),
                );
            }
        }
    }
}
