<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Fixture;

use Gawrys\Counterparty\Driver\DriverFactory;
use Gawrys\Counterparty\Registry\RegistryDriver;

/**
 * @implements DriverFactory<RegistryDriver>
 */
final class FakeRegistryDriverFactory implements DriverFactory
{
    public function __construct(
        private readonly string $name,
        private readonly RegistryDriver $driver,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function create(): RegistryDriver
    {
        return $this->driver;
    }
}
