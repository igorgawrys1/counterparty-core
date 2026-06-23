<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\WhiteList\HttpWhiteListClient;
use Gawrys\Counterparty\Adapter\WhiteList\WhiteListRegistryDriver;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\RegistryDriver;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Testing\RegistryDriverContractTestCase;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * The shipped reference White List registry driver must pass the public contract - the
 * same one third-party driver authors run.
 */
#[CoversClass(WhiteListRegistryDriver::class)]
final class WhiteListRegistryDriverContractTest extends RegistryDriverContractTestCase
{
    protected function createDriver(): RegistryDriver
    {
        $http = new MockHttp();
        $http->queueJson([
            'result' => [
                'subject' => ['name' => 'Acme', 'statusVat' => 'Czynny', 'accountNumbers' => []],
                'requestId' => 'req-1',
            ],
        ]);

        return new WhiteListRegistryDriver(
            new HttpWhiteListClient($http->jsonClient(), 'https://wl.example.test'),
            new FrozenClock(),
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
