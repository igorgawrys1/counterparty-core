<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\CounterpartyVerifierFactory;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use Gawrys\Counterparty\Verifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CounterpartyVerifierFactory::class)]
final class CounterpartyVerifierFactoryTest extends TestCase
{
    public function testCreateAssemblesAWorkingVerifier(): void
    {
        $http = new MockHttp();
        $http->setDefaultJson([]);
        // White List (active) then VIES (valid); sanctions falls through to the default [].
        $http->queueJson(['result' => ['subject' => ['name' => 'Acme', 'statusVat' => 'Czynny', 'accountNumbers' => []], 'requestId' => 'req-1']]);
        $http->queueJson(['valid' => true]);

        $verifier = CounterpartyVerifierFactory::create($http->client, $http->factory, $http->factory);
        $outcome = $verifier->verify(new Counterparty('Acme', 'PL', nip: '1234567890', euVat: 'PL1234567890'));

        $whiteList = $outcome->report->fromSource(Source::WHITE_LIST);
        self::assertNotSame([], $whiteList);
        self::assertSame(CheckStatus::Pass, $whiteList[0]->status);
    }

    public function testDiscoverBuildsAVerifier(): void
    {
        // Discovers the installed PSR-18 client + PSR-17 factories and wires the verifier;
        // construction must not throw (no HTTP is performed here).
        $this->expectNotToPerformAssertions();

        CounterpartyVerifierFactory::discover();
    }
}
