<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\WhiteList;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\AbstractRegistryDriver;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\LookupResult;
use Psr\Clock\ClockInterface;

/**
 * Exposes the PL White List on the capability-routed registry axis (VAT status and bank
 * account match), in addition to the richer {@see \Gawrys\Counterparty\Check\WhiteListCheck}.
 */
final readonly class WhiteListRegistryDriver extends AbstractRegistryDriver
{
    public function __construct(
        private WhiteListClient $client,
        private ClockInterface $clock,
    ) {
    }

    public function capabilities(): array
    {
        return [RegistryCapability::VatStatus, RegistryCapability::BankAccountMatch];
    }

    public function countries(): array
    {
        return ['PL'];
    }

    public function lookup(LookupRequest $request): LookupResult
    {
        $nip = $request->counterparty->nip;
        if ($nip === null) {
            return LookupResult::notFound();
        }

        $result = $this->client->searchByNip($nip, $this->clock->now());
        if (!$result->found) {
            return LookupResult::notFound('https://www.podatki.gov.pl/wykaz-podatnikow-vat-wyszukiwarka/');
        }

        return LookupResult::found(
            [
                'vatStatus' => $result->vatStatus->value,
                'name' => $result->name,
                'accountNumbers' => $result->accountNumbers,
            ],
            $result->requestId,
            'https://www.podatki.gov.pl/wykaz-podatnikow-vat-wyszukiwarka/',
        );
    }
}
