<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Registry;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * REGON / GUS statistical-register driver (PKD activity codes, legal form, status).
 *
 * The official GUS BIR service is SOAP-based and session-keyed; this driver targets a
 * JSON gateway exposing the same data. Provide the session/API key via $headers.
 *
 * @see https://api.stat.gov.pl/Home/RegonApi
 */
final readonly class RegonRegistryDriver extends AbstractPolishRegistryDriver
{
    public function capabilities(): array
    {
        return [RegistryCapability::StatisticalData, RegistryCapability::LegalEntityData];
    }

    protected function defaultBaseUri(): string
    {
        return 'https://wyszukiwarkaregon.stat.gov.pl';
    }

    protected function humanUri(): string
    {
        return 'https://wyszukiwarkaregon.stat.gov.pl/';
    }

    protected function endpoint(string $nip): string
    {
        return \sprintf('%s/api/regon/by-nip/%s', $this->baseUri, rawurlencode($nip));
    }

    protected function isFound(ArrayReader $reader): bool
    {
        return $reader->string('regon') !== null;
    }

    protected function proofId(ArrayReader $reader, string $nip): ?string
    {
        return $reader->string('regon');
    }
}
