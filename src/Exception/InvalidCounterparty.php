<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Exception;

final class InvalidCounterparty extends \InvalidArgumentException implements CounterpartyException
{
    public static function emptyName(): self
    {
        return new self('Counterparty name must not be empty.');
    }

    public static function invalidCountry(string $country): self
    {
        return new self(\sprintf(
            'Country must be an ISO-3166-1 alpha-2 code, got "%s".',
            $country,
        ));
    }
}
