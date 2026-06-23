<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\WhiteList;

/**
 * Outcome of a White List search. The {@see self::$requestId} is the official search
 * identifier that, with the request date, constitutes due-diligence proof.
 */
final readonly class WhiteListResult
{
    /**
     * @param list<string> $accountNumbers assigned 26-digit NRB account numbers
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public bool $found,
        public VatStatus $vatStatus,
        public ?string $name,
        public array $accountNumbers,
        public ?string $requestId,
        public ?string $krs = null,
        public array $raw = [],
    ) {
    }

    /**
     * Whether the given IBAN/NRB is among the entity's assigned accounts (compared on
     * digits only, so a leading "PL" country code does not matter).
     */
    public function hasAccount(string $iban): bool
    {
        $needle = self::digits($iban);
        if ($needle === '') {
            return false;
        }

        foreach ($this->accountNumbers as $account) {
            if (str_ends_with(self::digits($account), $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function digits(string $value): string
    {
        return (string) preg_replace('/\D+/', '', $value);
    }
}
