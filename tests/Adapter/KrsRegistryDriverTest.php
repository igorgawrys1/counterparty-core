<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\Registry\KrsRegistryDriver;
use Gawrys\Counterparty\Adapter\WhiteList\HttpWhiteListClient;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(KrsRegistryDriver::class)]
final class KrsRegistryDriverTest extends TestCase
{
    private function driver(MockHttp $http): KrsRegistryDriver
    {
        $client = $http->jsonClient();

        return new KrsRegistryDriver(
            $client,
            new HttpWhiteListClient($client, 'https://wl.example.test'),
            new FrozenClock(),
            'https://krs.example.test',
        );
    }

    private function request(): LookupRequest
    {
        return new LookupRequest(new Counterparty('Allegro', 'PL', nip: '5252674798'), RegistryCapability::LegalEntityData);
    }

    public function testResolvesNipToKrsThenFetchesExtract(): void
    {
        $http = new MockHttp();
        // 1) White List resolves NIP -> KRS.
        $http->queueJson(['result' => ['subject' => ['name' => 'Allegro', 'statusVat' => 'Czynny', 'accountNumbers' => [], 'krs' => '0000635012'], 'requestId' => 'wl-1']]);
        // 2) KRS extract by KRS number.
        $http->queueJson(['odpis' => ['naglowekA' => ['numerKRS' => '0000635012']]]);

        $result = $this->driver($http)->lookup($this->request());

        self::assertTrue($result->found);
        self::assertSame('0000635012', $result->proofId);
        self::assertArrayHasKey('odpis', $result->data);
    }

    public function testEntityWithoutKrsIsNotFound(): void
    {
        $http = new MockHttp();
        // White List with no KRS (e.g. a sole proprietor) -> no KRS lookup performed.
        $http->queueJson(['result' => ['subject' => ['name' => 'Jan Kowalski', 'statusVat' => 'Czynny', 'accountNumbers' => []], 'requestId' => 'wl-2']]);

        $result = $this->driver($http)->lookup($this->request());

        self::assertFalse($result->found);
    }
}
