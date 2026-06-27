<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Registry;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * Central Register of Beneficial Owners (Centralny Rejestr Beneficjentów Rzeczywistych).
 * Resolves the ultimate beneficial owners of a Polish entity by NIP.
 *
 * @see https://crbr.podatki.gov.pl/
 */
final readonly class CrbrRegistryDriver extends AbstractPolishRegistryDriver
{
    public function capabilities(): array
    {
        return [RegistryCapability::BeneficialOwners];
    }

    protected function defaultBaseUri(): string
    {
        return 'https://crbr.podatki.gov.pl';
    }

    protected function humanUri(): string
    {
        return 'https://crbr.podatki.gov.pl/';
    }

    protected function endpoint(string $nip): string
    {
        return \sprintf('%s/adcrbr/api/v1/podmiot/%s', $this->baseUri, rawurlencode($nip));
    }

    protected function isFound(ArrayReader $reader): bool
    {
        return $reader->each('beneficjenci') !== [];
    }

    protected function proofId(ArrayReader $reader, string $nip): ?string
    {
        return $reader->string('identyfikatorZgloszenia');
    }
}
