<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\WhiteList;

/**
 * VAT taxpayer status as reported by the PL White List ("wykaz podatników VAT").
 */
enum VatStatus: string
{
    case Active = 'active';
    case Exempt = 'exempt';
    case NotRegistered = 'not_registered';
    case Unknown = 'unknown';

    public static function fromApi(?string $statusVat): self
    {
        return match ($statusVat) {
            'Czynny' => self::Active,
            'Zwolniony' => self::Exempt,
            'Niezarejestrowany' => self::NotRegistered,
            default => self::Unknown,
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
