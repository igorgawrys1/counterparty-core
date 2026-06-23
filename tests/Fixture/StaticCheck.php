<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Fixture;

use Gawrys\Counterparty\Check\Check;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;

/**
 * A check that returns a preconfigured result, for orchestration tests.
 */
final class StaticCheck implements Check
{
    public function __construct(
        private readonly CheckResult $result,
        private readonly bool $supported = true,
    ) {
    }

    public function name(): string
    {
        return $this->result->checkName;
    }

    public function source(): string
    {
        return $this->result->source;
    }

    public function supports(Counterparty $counterparty): bool
    {
        return $this->supported;
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        return $this->result;
    }
}
