<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Check;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\RegistryManager;
use Gawrys\Counterparty\Report\CheckResult;
use Psr\Clock\ClockInterface;

/**
 * Capability-routed check: asks the {@see RegistryManager} which driver can answer a
 * capability for the counterparty's country. When none can, it reports an honest
 * inconclusive result with the reason - never a guess. Adding a country/registry that
 * covers the capability makes this check start working with zero changes here.
 */
final readonly class RegistryCheck implements Check
{
    public function __construct(
        private RegistryManager $registries,
        private RegistryCapability $capability,
        private ClockInterface $clock,
        private string $source,
        private string $name,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function supports(Counterparty $counterparty): bool
    {
        return true;
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        $now = $this->clock->now();
        $driver = $this->registries->driverFor($counterparty->country, $this->capability);

        if ($driver === null) {
            return CheckResult::inconclusive(
                $this->source,
                $this->name,
                \sprintf(
                    'No registry driver covers %s for country %s.',
                    $this->capability->label(),
                    $counterparty->country,
                ),
                $now,
            );
        }

        $result = $driver->lookup(new LookupRequest($counterparty, $this->capability));

        if (!$result->found) {
            return CheckResult::warning(
                $this->source,
                $this->name,
                \sprintf('No %s record found for the counterparty.', $this->capability->label()),
                $now,
                $result->data,
                $result->proofId,
            );
        }

        return CheckResult::pass(
            $this->source,
            $this->name,
            \sprintf('%s confirmed in the registry.', $this->capability->label()),
            $now,
            $result->data,
            $result->proofId,
        );
    }
}
