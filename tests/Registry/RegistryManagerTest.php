<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Registry;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\AbstractRegistryDriver;
use Gawrys\Counterparty\Registry\RegistryManager;
use Gawrys\Counterparty\Tests\Fixture\FakeRegistryDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistryManager::class)]
#[CoversClass(AbstractRegistryDriver::class)]
final class RegistryManagerTest extends TestCase
{
    private RegistryManager $manager;

    protected function setUp(): void
    {
        $this->manager = new RegistryManager();
        $this->manager->extend('pl-white-list', static fn (): FakeRegistryDriver => new FakeRegistryDriver(
            [RegistryCapability::VatStatus, RegistryCapability::BankAccountMatch],
            ['PL'],
        ));
        $this->manager->extend('vies', static fn (): FakeRegistryDriver => new FakeRegistryDriver(
            [RegistryCapability::EuVatValidation],
            ['PL', 'DE', 'FR'],
        ));
    }

    public function testDriverForRoutesByCountryAndCapability(): void
    {
        $driver = $this->manager->driverFor('DE', RegistryCapability::EuVatValidation);

        self::assertNotNull($driver);
        self::assertContains('DE', $driver->countries());
    }

    public function testDriverForReturnsNullWhenUncovered(): void
    {
        self::assertNull($this->manager->driverFor('PL', RegistryCapability::BeneficialOwners));
        self::assertFalse($this->manager->covers('PL', RegistryCapability::BeneficialOwners));
    }

    public function testCoversReflectsAvailability(): void
    {
        self::assertTrue($this->manager->covers('PL', RegistryCapability::VatStatus));
        self::assertTrue($this->manager->covers('FR', RegistryCapability::EuVatValidation));
        self::assertFalse($this->manager->covers('US', RegistryCapability::VatStatus));
    }

    public function testLookupRoutesAndReturnsResult(): void
    {
        $counterparty = new Counterparty('Acme', 'PL', nip: '1234567890');

        $result = $this->manager->lookup($counterparty, RegistryCapability::VatStatus);

        self::assertNotNull($result);
        self::assertTrue($result->found);
        self::assertSame('PL', $result->data['country']);
    }

    public function testLookupReturnsNullWhenUncovered(): void
    {
        $counterparty = new Counterparty('Acme', 'US');

        self::assertNull($this->manager->lookup($counterparty, RegistryCapability::VatStatus));
    }

    public function testFirstRegisteredCoveringDriverWins(): void
    {
        // Both pl-white-list and vies serve PL; for a capability only the first has,
        // routing must pick that one deterministically.
        $driver = $this->manager->driverFor('PL', RegistryCapability::BankAccountMatch);

        self::assertNotNull($driver);
        self::assertContains(RegistryCapability::BankAccountMatch, $driver->capabilities());
    }
}
