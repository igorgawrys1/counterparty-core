<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Check;

use Gawrys\Counterparty\Adapter\Vies\ViesClient;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Psr\Clock\ClockInterface;

/**
 * Validates an EU VAT number through VIES. Applicable to any counterparty carrying an
 * EU VAT identifier, regardless of country.
 */
final readonly class ViesCheck implements Check
{
    public function __construct(
        private ViesClient $client,
        private ClockInterface $clock,
    ) {
    }

    public function name(): string
    {
        return 'EU VIES';
    }

    public function source(): string
    {
        return Source::VIES;
    }

    public function supports(Counterparty $counterparty): bool
    {
        return $counterparty->hasEuVat() && \strlen($counterparty->euVat ?? '') > 2;
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        $euVat = $counterparty->euVat ?? '';
        $countryCode = substr($euVat, 0, 2);
        $vatNumber = substr($euVat, 2);
        $now = $this->clock->now();

        $result = $this->client->validate($countryCode, $vatNumber);
        $raw = ['valid' => $result->valid, 'name' => $result->name, 'address' => $result->address];

        if (!$result->valid) {
            return CheckResult::fail(
                Source::VIES,
                $this->name(),
                \sprintf('EU VAT number %s is not valid in VIES.', $euVat),
                $now,
                $raw,
            );
        }

        return CheckResult::pass(
            Source::VIES,
            $this->name(),
            \sprintf('EU VAT number %s is valid in VIES.', $euVat),
            $now,
            $raw,
        );
    }
}
