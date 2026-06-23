<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Enum;

enum RiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    /**
     * Map a normalised risk score (0.0 = no risk, 1.0 = maximum risk) onto a level.
     */
    public static function fromScore(float $score): self
    {
        return match (true) {
            $score >= 0.8 => self::Critical,
            $score >= 0.5 => self::High,
            $score >= 0.25 => self::Medium,
            default => self::Low,
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::Low => 0,
            self::Medium => 1,
            self::High => 2,
            self::Critical => 3,
        };
    }

    public function isAtLeast(self $other): bool
    {
        return $this->weight() >= $other->weight();
    }
}
