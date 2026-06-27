<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Driver;

use Gawrys\Counterparty\Driver\AbstractDriverManager;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Exception\UnknownDriver;
use Gawrys\Counterparty\Registry\RegistryManager;
use Gawrys\Counterparty\Tests\Fixture\FakeRegistryDriver;
use Gawrys\Counterparty\Tests\Fixture\FakeRegistryDriverFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractDriverManager::class)]
#[CoversClass(UnknownDriver::class)]
final class AbstractDriverManagerTest extends TestCase
{
    public function testExtendAndResolveByName(): void
    {
        $manager = new RegistryManager();
        $driver = new FakeRegistryDriver([RegistryCapability::VatStatus], ['PL']);
        $manager->extend('pl', static fn (): FakeRegistryDriver => $driver);

        self::assertTrue($manager->has('pl'));
        self::assertSame($driver, $manager->driver('pl'));
        self::assertSame(['pl'], $manager->names());
    }

    public function testNameResolutionIsCaseInsensitive(): void
    {
        $manager = new RegistryManager();
        $driver = new FakeRegistryDriver([RegistryCapability::VatStatus], ['PL']);
        $manager->extend('  PL ', static fn (): FakeRegistryDriver => $driver);

        self::assertTrue($manager->has('pl'));
        self::assertSame($driver, $manager->driver('PL'));
    }

    public function testDriversAreLazilyInstantiatedOnceAndMemoised(): void
    {
        $manager = new RegistryManager();
        $counter = new class () {
            private int $calls = 0;

            public function bump(): void
            {
                ++$this->calls;
            }

            public function calls(): int
            {
                return $this->calls;
            }
        };
        $manager->extend('pl', function () use ($counter): FakeRegistryDriver {
            $counter->bump();

            return new FakeRegistryDriver([RegistryCapability::VatStatus], ['PL']);
        });

        self::assertSame(0, $counter->calls(), 'Factory must not run until the driver is requested.');

        $first = $manager->driver('pl');
        $second = $manager->driver('pl');

        self::assertSame($first, $second);
        self::assertSame(1, $counter->calls(), 'Factory must run at most once per driver.');
    }

    public function testRegisterViaDriverFactory(): void
    {
        $manager = new RegistryManager();
        $driver = new FakeRegistryDriver([RegistryCapability::EuVatValidation], ['DE']);
        $manager->register(new FakeRegistryDriverFactory('de', $driver));

        self::assertSame($driver, $manager->driver('de'));
    }

    public function testUnknownDriverThrows(): void
    {
        $this->expectException(UnknownDriver::class);

        (new RegistryManager())->driver('nope');
    }
}
