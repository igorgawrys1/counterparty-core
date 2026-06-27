<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Check;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;

/**
 * A single deterministic verification step producing one hard fact.
 *
 * Implementations should return an inconclusive {@see CheckResult} for expected provider
 * conditions (missing data, unsupported country, transport failure) rather than throwing;
 * the {@see \Gawrys\Counterparty\Verifier} is a safety net for the unexpected only.
 */
interface Check
{
    /** Human-readable name, e.g. "PL White List". */
    public function name(): string;

    /** Stable source identifier recorded on every {@see CheckResult}, e.g. "pl.white_list". */
    public function source(): string;

    /** Whether this check is applicable to the given counterparty (e.g. has the required identifier). */
    public function supports(Counterparty $counterparty): bool;

    public function run(Counterparty $counterparty): CheckResult;
}
