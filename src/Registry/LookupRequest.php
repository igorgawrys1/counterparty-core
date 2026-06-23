<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Registry;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;

/**
 * A request for one capability about one counterparty, routed to a driver that covers it.
 */
final readonly class LookupRequest
{
    public function __construct(
        public Counterparty $counterparty,
        public RegistryCapability $capability,
    ) {
    }
}
