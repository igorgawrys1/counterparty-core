<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Exception;

final class InvalidEvidence extends \InvalidArgumentException implements CounterpartyException
{
    public static function confidenceOutOfRange(float $confidence): self
    {
        return new self(\sprintf(
            'Evidence confidence must be within [0.0, 1.0], got %.4f.',
            $confidence,
        ));
    }

    public static function emptyClaim(): self
    {
        return new self('Evidence claim must not be empty.');
    }
}
