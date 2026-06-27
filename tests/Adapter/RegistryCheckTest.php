<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\Registry\CrbrRegistryDriver;
use Gawrys\Counterparty\Check\RegistryCheck;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\RegistryManager;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegistryCheck::class)]
#[CoversClass(CrbrRegistryDriver::class)]
#[CoversClass(\Gawrys\Counterparty\Adapter\Registry\AbstractPolishRegistryDriver::class)]
final class RegistryCheckTest extends TestCase
{
    public function testReturnsInconclusiveWhenNoDriverCoversCapability(): void
    {
        $check = new RegistryCheck(
            new RegistryManager(),
            RegistryCapability::BeneficialOwners,
            new FrozenClock(),
            Source::CRBR,
            'PL CRBR',
        );

        $result = $check->run(new Counterparty('Acme', 'PL', nip: '1234567890'));

        self::assertSame(CheckStatus::Inconclusive, $result->status);
        self::assertStringContainsString('No registry driver covers', $result->summary);
    }

    public function testPassesWhenBeneficialOwnersFound(): void
    {
        $http = new MockHttp();
        $http->queueJson([
            'beneficjenci' => [['imie' => 'Jan', 'nazwisko' => 'Kowalski']],
            'identyfikatorZgloszenia' => 'crbr-77',
        ]);

        $manager = new RegistryManager();
        $manager->extend('crbr', static fn (): CrbrRegistryDriver => new CrbrRegistryDriver($http->jsonClient(), 'https://crbr.example.test'));

        $check = new RegistryCheck($manager, RegistryCapability::BeneficialOwners, new FrozenClock(), Source::CRBR, 'PL CRBR');
        $result = $check->run(new Counterparty('Acme', 'PL', nip: '1234567890'));

        self::assertSame(CheckStatus::Pass, $result->status);
        self::assertSame('crbr-77', $result->proofId);
    }

    public function testWarnsWhenRecordNotFound(): void
    {
        $http = new MockHttp();
        $http->queueJson(['beneficjenci' => []]);

        $manager = new RegistryManager();
        $manager->extend('crbr', static fn (): CrbrRegistryDriver => new CrbrRegistryDriver($http->jsonClient(), 'https://crbr.example.test'));

        $check = new RegistryCheck($manager, RegistryCapability::BeneficialOwners, new FrozenClock(), Source::CRBR, 'PL CRBR');
        $result = $check->run(new Counterparty('Acme', 'PL', nip: '1234567890'));

        self::assertSame(CheckStatus::Warning, $result->status);
    }
}
