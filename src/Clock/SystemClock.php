<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Clock;

use Psr\Clock\ClockInterface;

/**
 * Default PSR-20 clock backed by the system time. Use {@see \Gawrys\Counterparty\Testing\FrozenClock}
 * in tests for determinism.
 */
final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}
