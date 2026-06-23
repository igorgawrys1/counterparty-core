<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Registry;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\RegistryDriver;
use Gawrys\Counterparty\Testing\RegistryDriverContractTestCase;
use Gawrys\Counterparty\Tests\Fixture\FakeRegistryDriver;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Proves the shipped contract test case is green against a conforming driver — the gate
 * for Phase 2.
 */
#[CoversClass(RegistryDriverContractTestCase::class)]
#[CoversClass(FakeRegistryDriver::class)]
final class FakeRegistryDriverContractTest extends RegistryDriverContractTestCase
{
    protected function createDriver(): RegistryDriver
    {
        return new FakeRegistryDriver(
            [RegistryCapability::VatStatus, RegistryCapability::LegalEntityData],
            ['PL'],
        );
    }

    protected function supportedRequest(): LookupRequest
    {
        return new LookupRequest(
            new Counterparty('Acme', 'PL', nip: '1234567890'),
            RegistryCapability::VatStatus,
        );
    }
}
