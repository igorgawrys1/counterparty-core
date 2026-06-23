<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\Sanctions\SanctionsNetworkProvider;
use Gawrys\Counterparty\Check\SanctionsScreeningCheck;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SanctionsScreeningCheck::class)]
#[CoversClass(SanctionsNetworkProvider::class)]
final class SanctionsScreeningCheckTest extends TestCase
{
    private MockHttp $http;
    private SanctionsScreeningCheck $check;

    protected function setUp(): void
    {
        $this->http = new MockHttp();
        $this->check = new SanctionsScreeningCheck(
            new SanctionsNetworkProvider($this->http->jsonClient(), 'https://sn.example.test'),
            new FrozenClock(),
        );
    }

    public function testAlwaysApplicable(): void
    {
        self::assertTrue($this->check->supports(new Counterparty('Anyone', 'PL')));
    }

    public function testServerSideMatchFails(): void
    {
        // sanctions.network returns a top-level list of matched rows from search_sanctions.
        $this->http->queueJson([
            ['names' => ['Blocked Entity', 'Blocked Ent.'], 'source' => 'ofac', 'target_type' => 'entity'],
        ]);

        $result = $this->check->run(new Counterparty('Blocked Entity', 'RU'));

        self::assertSame(CheckStatus::Fail, $result->status);
    }

    public function testNoResultsPasses(): void
    {
        $this->http->queueJson([]);

        $result = $this->check->run(new Counterparty('Acme', 'PL'));

        self::assertSame(CheckStatus::Pass, $result->status);
    }
}
