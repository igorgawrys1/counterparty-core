<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Enum;

enum CheckStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Warning = 'warning';
    case Inconclusive = 'inconclusive';

    /**
     * A finding that should weigh against the counterparty (a hard negative fact).
     */
    public function isAdverse(): bool
    {
        return $this === self::Fail;
    }

    /**
     * A finding the library could not determine - never to be treated as a pass.
     */
    public function isConclusive(): bool
    {
        return $this !== self::Inconclusive;
    }

    /**
     * Relative severity used to compute the worst status of a report.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Pass => 0,
            self::Inconclusive => 1,
            self::Warning => 2,
            self::Fail => 3,
        };
    }
}
