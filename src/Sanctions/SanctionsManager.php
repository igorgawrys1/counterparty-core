<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Sanctions;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Driver\AbstractDriverManager;

/**
 * Resolves named sanctions providers. Unlike registries these are not country-routed;
 * the application screens against the provider(s) it has licensed.
 *
 * @extends AbstractDriverManager<SanctionsProvider>
 */
final class SanctionsManager extends AbstractDriverManager
{
    public function screen(string $provider, Counterparty $counterparty): SanctionsResult
    {
        return $this->driver($provider)->screen($counterparty);
    }
}
