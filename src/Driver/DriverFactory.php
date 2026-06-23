<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Driver;

/**
 * DI-friendly, one-class-per-driver registration path.
 *
 * Each factory names the driver it produces and knows how to build it from its own
 * injected configuration/dependencies. In Symfony these are tagged services collected by
 * a compiler pass; in plain PHP they are registered directly on a manager.
 *
 * @template T of object
 */
interface DriverFactory
{
    /** The name the produced driver is registered under. */
    public function name(): string;

    /**
     * @return T
     */
    public function create(): object;
}
