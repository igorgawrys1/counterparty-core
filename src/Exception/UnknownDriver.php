<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Exception;

final class UnknownDriver extends \OutOfBoundsException implements CounterpartyException
{
    /**
     * @param list<string> $known
     */
    public static function named(string $name, array $known): self
    {
        return new self(\sprintf(
            'No driver registered under "%s". Known drivers: %s.',
            $name,
            $known === [] ? '(none)' : implode(', ', $known),
        ));
    }
}
