<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Registry;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Driver\AbstractDriverManager;
use Gawrys\Counterparty\Enum\RegistryCapability;

/**
 * Resolves registry drivers and routes by (country + required capability).
 *
 * @extends AbstractDriverManager<RegistryDriver>
 */
final class RegistryManager extends AbstractDriverManager
{
    /**
     * The first registered driver that covers the (country, capability) pair, or null
     * when none does — the caller then reports an honest inconclusive result.
     */
    public function driverFor(string $country, RegistryCapability $capability): ?RegistryDriver
    {
        foreach ($this->all() as $driver) {
            if ($driver->supports($country, $capability)) {
                return $driver;
            }
        }

        return null;
    }

    public function covers(string $country, RegistryCapability $capability): bool
    {
        return $this->driverFor($country, $capability) !== null;
    }

    /**
     * Route a lookup for the counterparty's country, or null when uncovered.
     */
    public function lookup(Counterparty $counterparty, RegistryCapability $capability): ?LookupResult
    {
        $driver = $this->driverFor($counterparty->country, $capability);

        return $driver?->lookup(new LookupRequest($counterparty, $capability));
    }
}
