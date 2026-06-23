<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Sanctions;

use Gawrys\Counterparty\Counterparty;

/**
 * A sanctions/PEP screening source. Reference adapter targets sanctions.network (free);
 * an OpenSanctions adapter is available behind a commercial-license note.
 */
interface SanctionsProvider
{
    public function screen(Counterparty $counterparty): SanctionsResult;
}
