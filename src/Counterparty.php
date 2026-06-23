<?php

declare(strict_types=1);

namespace Gawrys\Counterparty;

use Gawrys\Counterparty\Exception\InvalidCounterparty;

/**
 * Immutable description of the entity being verified.
 *
 * All identifiers are normalised on construction (trimmed, separators stripped,
 * upper-cased where applicable) so downstream adapters and the cache key are stable.
 */
final readonly class Counterparty
{
    /** ISO-3166-1 alpha-2 country code, upper-cased. */
    public string $country;

    /** Polish tax identification number (NIP), digits only, or null. */
    public ?string $nip;

    /** International Bank Account Number, normalised (no spaces, upper-cased), or null. */
    public ?string $iban;

    /** EU VAT number (country prefix + digits), normalised, or null. */
    public ?string $euVat;

    public string $name;

    public function __construct(
        string $name,
        string $country,
        ?string $nip = null,
        ?string $iban = null,
        ?string $euVat = null,
    ) {
        $name = trim($name);
        if ($name === '') {
            throw InvalidCounterparty::emptyName();
        }

        $country = strtoupper(trim($country));
        if (preg_match('/^[A-Z]{2}$/', $country) !== 1) {
            throw InvalidCounterparty::invalidCountry($country);
        }

        $this->name = $name;
        $this->country = $country;
        $this->nip = self::normaliseDigits($nip);
        $this->iban = self::normaliseAlnum($iban);
        $this->euVat = self::normaliseAlnum($euVat);
    }

    /**
     * Stable, side-channel-free fingerprint suitable for cache keys. Derived only from
     * normalised identifiers; carries no free-form PII such as the display name.
     */
    public function fingerprint(): string
    {
        return hash('sha256', implode('|', [
            $this->country,
            $this->nip ?? '',
            $this->iban ?? '',
            $this->euVat ?? '',
        ]));
    }

    public function hasNip(): bool
    {
        return $this->nip !== null;
    }

    public function hasIban(): bool
    {
        return $this->iban !== null;
    }

    public function hasEuVat(): bool
    {
        return $this->euVat !== null;
    }

    private static function normaliseDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? null : $digits;
    }

    private static function normaliseAlnum(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', $value));

        return $clean === '' ? null : $clean;
    }
}
