<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Driver;

use Gawrys\Counterparty\Exception\UnknownDriver;

/**
 * One shared manager primitive reused across every driver axis (registries, sanctions
 * providers, AI providers, research tools). The mechanism is abstracted; the driver
 * shapes are not — each axis extends this with its own driver interface as the template
 * parameter.
 *
 * Two registration paths, both backed by the same resolution and lazy, memoised
 * instantiation:
 *  - {@see self::extend()}   — closure-based, Laravel-style DX for application code;
 *  - {@see self::register()} — a {@see DriverFactory}, DI-friendly and taggable in Symfony.
 *
 * @template T of object
 */
abstract class AbstractDriverManager
{
    /** @var array<string, \Closure(): T> */
    private array $factories = [];

    /** @var array<string, T> */
    private array $instances = [];

    /**
     * @param callable(): T $factory
     */
    final public function extend(string $name, callable $factory): void
    {
        $key = $this->normalise($name);
        $this->factories[$key] = $factory(...);
        unset($this->instances[$key]);
    }

    /**
     * @param DriverFactory<T> $factory
     */
    final public function register(DriverFactory $factory): void
    {
        $this->extend(
            $factory->name(),
            /** @return T */
            static fn (): object => $factory->create(),
        );
    }

    /**
     * @return T
     */
    final public function driver(string $name): object
    {
        $key = $this->normalise($name);
        if (!isset($this->factories[$key])) {
            throw UnknownDriver::named($name, $this->names());
        }

        return $this->instances[$key] ??= ($this->factories[$key])();
    }

    final public function has(string $name): bool
    {
        return isset($this->factories[$this->normalise($name)]);
    }

    /**
     * @return list<string>
     */
    final public function names(): array
    {
        return array_keys($this->factories);
    }

    /**
     * @return \Generator<string, T>
     */
    final protected function all(): \Generator
    {
        foreach ($this->names() as $name) {
            yield $name => $this->driver($name);
        }
    }

    protected function normalise(string $name): string
    {
        return strtolower(trim($name));
    }
}
