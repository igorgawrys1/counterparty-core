<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Exception\InvalidCounterparty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Counterparty::class)]
final class CounterpartyTest extends TestCase
{
    public function testItNormalisesIdentifiers(): void
    {
        $counterparty = new Counterparty(
            name: '  Acme Sp. z o.o. ',
            country: 'pl',
            nip: '123-456-78-90',
            iban: 'pl61 1090 1014 0000 0712 1981 2874',
            euVat: 'pl1234567890',
        );

        self::assertSame('Acme Sp. z o.o.', $counterparty->name);
        self::assertSame('PL', $counterparty->country);
        self::assertSame('1234567890', $counterparty->nip);
        self::assertSame('PL61109010140000071219812874', $counterparty->iban);
        self::assertSame('PL1234567890', $counterparty->euVat);
    }

    public function testOptionalIdentifiersDefaultToNull(): void
    {
        $counterparty = new Counterparty('Acme', 'DE');

        self::assertNull($counterparty->nip);
        self::assertFalse($counterparty->hasNip());
        self::assertFalse($counterparty->hasIban());
        self::assertFalse($counterparty->hasEuVat());
    }

    public function testFingerprintIsStableAndExcludesName(): void
    {
        $a = new Counterparty('Acme', 'PL', nip: '1234567890');
        $b = new Counterparty('Different Name', 'PL', nip: '123 456 78 90');

        self::assertSame($a->fingerprint(), $b->fingerprint());
    }

    public function testFingerprintChangesWithIdentifiers(): void
    {
        $a = new Counterparty('Acme', 'PL', nip: '1234567890');
        $b = new Counterparty('Acme', 'PL', nip: '9999999999');

        self::assertNotSame($a->fingerprint(), $b->fingerprint());
    }

    public function testItRejectsEmptyName(): void
    {
        $this->expectException(InvalidCounterparty::class);

        new Counterparty('   ', 'PL');
    }

    public function testItRejectsInvalidCountry(): void
    {
        $this->expectException(InvalidCounterparty::class);

        new Counterparty('Acme', 'Poland');
    }

    public function testBlankIdentifiersBecomeNull(): void
    {
        $counterparty = new Counterparty('Acme', 'PL', nip: '---', iban: '   ');

        self::assertNull($counterparty->nip);
        self::assertNull($counterparty->iban);
    }
}
