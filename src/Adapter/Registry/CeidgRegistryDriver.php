<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Registry;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * CEIDG driver - the register of sole proprietors and civil-law partnerships.
 *
 * The biznes.gov.pl API requires a bearer token; pass it as the "Authorization" header
 * via the $headers constructor argument.
 *
 * @see https://dane.biznes.gov.pl/
 */
final readonly class CeidgRegistryDriver extends AbstractPolishRegistryDriver
{
    public function capabilities(): array
    {
        return [RegistryCapability::LegalEntityData, RegistryCapability::BusinessRegistration];
    }

    protected function defaultBaseUri(): string
    {
        return 'https://dane.biznes.gov.pl';
    }

    protected function humanUri(): string
    {
        return 'https://aplikacja.ceidg.gov.pl/';
    }

    protected function endpoint(string $nip): string
    {
        return \sprintf('%s/api/ceidg/v3/firmy?nip=%s', $this->baseUri, rawurlencode($nip));
    }

    protected function isFound(ArrayReader $reader): bool
    {
        return $reader->each('firma') !== [];
    }

    protected function proofId(ArrayReader $reader, string $nip): ?string
    {
        $companies = $reader->each('firma');

        return $companies === [] ? null : $companies[0]->string('id');
    }
}
