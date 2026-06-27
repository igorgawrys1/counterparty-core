<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Check;

use Gawrys\Counterparty\Adapter\WhiteList\VatStatus;
use Gawrys\Counterparty\Adapter\WhiteList\WhiteListClient;
use Gawrys\Counterparty\Clock\SystemClock;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Psr\Clock\ClockInterface;

/**
 * Verifies a Polish counterparty's VAT status against the White List and, when an IBAN is
 * supplied, whether it is among the entity's registered accounts. Persists the official
 * search identifier as due-diligence proof.
 */
final readonly class WhiteListCheck implements Check
{
    private ClockInterface $clock;

    public function __construct(
        private WhiteListClient $client,
        ?ClockInterface $clock = null,
    ) {
        $this->clock = $clock ?? new SystemClock();
    }

    public function name(): string
    {
        return 'PL White List';
    }

    public function source(): string
    {
        return Source::WHITE_LIST;
    }

    public function supports(Counterparty $counterparty): bool
    {
        return $counterparty->country === 'PL' && $counterparty->hasNip();
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        $nip = $counterparty->nip ?? '';
        $now = $this->clock->now();
        $result = $this->client->searchByNip($nip, $now);

        $bankAccountAssigned = $counterparty->hasIban()
            ? $result->hasAccount($counterparty->iban ?? '')
            : null;

        $raw = [
            'vatStatus' => $result->vatStatus->value,
            'name' => $result->name,
            'accountNumbers' => $result->accountNumbers,
            'bankAccountAssigned' => $bankAccountAssigned,
        ];

        if (!$result->found || $result->vatStatus === VatStatus::NotRegistered) {
            return CheckResult::fail(
                Source::WHITE_LIST,
                $this->name(),
                'The counterparty is not registered as an active VAT payer.',
                $now,
                $raw,
                $result->requestId,
            );
        }

        if ($result->vatStatus === VatStatus::Exempt) {
            return CheckResult::warning(
                Source::WHITE_LIST,
                $this->name(),
                'The counterparty is VAT-exempt.',
                $now,
                $raw,
                $result->requestId,
            );
        }

        if ($bankAccountAssigned === false) {
            return CheckResult::warning(
                Source::WHITE_LIST,
                $this->name(),
                'Active VAT payer, but the provided IBAN is not assigned to the counterparty.',
                $now,
                $raw,
                $result->requestId,
            );
        }

        return CheckResult::pass(
            Source::WHITE_LIST,
            $this->name(),
            'Active VAT payer.',
            $now,
            $raw,
            $result->requestId,
        );
    }
}
