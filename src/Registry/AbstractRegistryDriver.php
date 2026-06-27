<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Registry;

use Gawrys\Counterparty\Enum\RegistryCapability;

/**
 * Convenience base deriving {@see RegistryDriver::supports()} from the declared
 * capabilities and countries, so concrete drivers only implement the two declarations
 * plus {@see RegistryDriver::lookup()}. Keeps support logic consistent across drivers
 * (and consistent with the contract test case).
 */
abstract readonly class AbstractRegistryDriver implements RegistryDriver
{
    public function supports(string $country, RegistryCapability $capability): bool
    {
        return \in_array(strtoupper(trim($country)), $this->countries(), true)
            && \in_array($capability, $this->capabilities(), true);
    }
}
