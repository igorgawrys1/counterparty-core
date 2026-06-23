<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Registry;

use Gawrys\Counterparty\Enum\RegistryCapability;

/**
 * A per-country registry adapter that DECLARES what it can answer.
 *
 * Adding a country or registry is one new driver plus its registration — no changes to
 * the core, the checks, or the framework bridges. A driver must never claim a capability
 * it does not genuinely implement: the verifier trusts {@see self::supports()} and routes
 * accordingly, returning an inconclusive result when nothing covers a request.
 */
interface RegistryDriver
{
    /**
     * @return list<RegistryCapability> the capabilities this driver genuinely implements
     */
    public function capabilities(): array;

    /**
     * @return list<string> ISO-3166-1 alpha-2 country codes served, upper-cased
     */
    public function countries(): array;

    public function supports(string $country, RegistryCapability $capability): bool;

    public function lookup(LookupRequest $request): LookupResult;
}
