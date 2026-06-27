<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Fixture;

use Gawrys\Counterparty\Check\Check;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;

/**
 * A check that always throws, to exercise the verifier's safety net.
 */
final class ThrowingCheck implements Check
{
    public function name(): string
    {
        return 'Throwing Check';
    }

    public function source(): string
    {
        return 'test.throwing';
    }

    public function supports(Counterparty $counterparty): bool
    {
        return true;
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        throw new \RuntimeException('boom');
    }
}
