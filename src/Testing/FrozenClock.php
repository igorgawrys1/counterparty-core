<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Testing;

use Psr\Clock\ClockInterface;

/**
 * A PSR-20 clock that always returns the same instant. Ships in `src` so consumers can
 * use it to write deterministic tests for their own checks and drivers.
 */
final class FrozenClock implements ClockInterface
{
    private \DateTimeImmutable $now;

    public function __construct(?\DateTimeImmutable $now = null)
    {
        $this->now = $now ?? new \DateTimeImmutable('2024-01-01T00:00:00+00:00');
    }

    public static function at(string $iso8601): self
    {
        return new self(new \DateTimeImmutable($iso8601));
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
