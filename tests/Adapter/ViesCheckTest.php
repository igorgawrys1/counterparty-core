<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\Vies\HttpViesClient;
use Gawrys\Counterparty\Adapter\Vies\ViesResult;
use Gawrys\Counterparty\Check\ViesCheck;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViesCheck::class)]
#[CoversClass(HttpViesClient::class)]
#[CoversClass(ViesResult::class)]
final class ViesCheckTest extends TestCase
{
    private MockHttp $http;
    private ViesCheck $check;

    protected function setUp(): void
    {
        $this->http = new MockHttp();
        $this->check = new ViesCheck(
            new HttpViesClient($this->http->jsonClient(), 'https://vies.example.test'),
            new FrozenClock(),
        );
    }

    public function testSupportsCounterpartiesWithEuVat(): void
    {
        self::assertTrue($this->check->supports(new Counterparty('Acme', 'PL', euVat: 'PL1234567890')));
        self::assertFalse($this->check->supports(new Counterparty('Acme', 'PL')));
    }

    public function testValidVatNumberPasses(): void
    {
        $this->http->queueJson(['valid' => true, 'name' => 'ACME', 'address' => 'Warsaw']);

        $result = $this->check->run(new Counterparty('Acme', 'PL', euVat: 'PL1234567890'));

        self::assertSame(CheckStatus::Pass, $result->status);
    }

    public function testInvalidVatNumberFails(): void
    {
        $this->http->queueJson(['valid' => false]);

        $result = $this->check->run(new Counterparty('Acme', 'PL', euVat: 'PL0000000000'));

        self::assertSame(CheckStatus::Fail, $result->status);
    }
}
