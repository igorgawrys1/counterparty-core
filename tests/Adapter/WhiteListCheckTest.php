<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Adapter;

use Gawrys\Counterparty\Adapter\WhiteList\HttpWhiteListClient;
use Gawrys\Counterparty\Adapter\WhiteList\VatStatus;
use Gawrys\Counterparty\Adapter\WhiteList\WhiteListResult;
use Gawrys\Counterparty\Check\WhiteListCheck;
use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WhiteListCheck::class)]
#[CoversClass(HttpWhiteListClient::class)]
#[CoversClass(WhiteListResult::class)]
#[CoversClass(VatStatus::class)]
final class WhiteListCheckTest extends TestCase
{
    private const NRB = '61109010140000071219812874';

    private MockHttp $http;
    private WhiteListCheck $check;

    protected function setUp(): void
    {
        $this->http = new MockHttp();
        $this->check = new WhiteListCheck(
            new HttpWhiteListClient($this->http->jsonClient(), 'https://wl.example.test'),
            new FrozenClock(),
        );
    }

    public function testSupportsOnlyPolishCounterpartiesWithNip(): void
    {
        self::assertTrue($this->check->supports(new Counterparty('Acme', 'PL', nip: '1234567890')));
        self::assertFalse($this->check->supports(new Counterparty('Acme', 'PL')));
        self::assertFalse($this->check->supports(new Counterparty('Acme', 'DE', nip: '1234567890')));
    }

    public function testActiveVatPayerWithAssignedAccountPasses(): void
    {
        $this->queueSubject('Czynny', [self::NRB], 'req-123');

        $result = $this->check->run(new Counterparty('Acme', 'PL', nip: '1234567890', iban: 'PL' . self::NRB));

        self::assertSame(CheckStatus::Pass, $result->status);
        self::assertSame('req-123', $result->proofId);
        self::assertTrue($result->raw['bankAccountAssigned']);
    }

    public function testActiveVatPayerWithUnassignedAccountWarns(): void
    {
        $this->queueSubject('Czynny', ['00000000000000000000000000'], 'req-9');

        $result = $this->check->run(new Counterparty('Acme', 'PL', nip: '1234567890', iban: 'PL' . self::NRB));

        self::assertSame(CheckStatus::Warning, $result->status);
        self::assertFalse($result->raw['bankAccountAssigned']);
    }

    public function testExemptPayerWarns(): void
    {
        $this->queueSubject('Zwolniony', [], 'req-2');

        $result = $this->check->run(new Counterparty('Acme', 'PL', nip: '1234567890'));

        self::assertSame(CheckStatus::Warning, $result->status);
    }

    public function testUnregisteredPayerFails(): void
    {
        $this->http->queueJson(['result' => ['requestId' => 'req-3']]);

        $result = $this->check->run(new Counterparty('Acme', 'PL', nip: '1234567890'));

        self::assertSame(CheckStatus::Fail, $result->status);
        self::assertSame('req-3', $result->proofId);
    }

    /**
     * @param list<string> $accountNumbers
     */
    private function queueSubject(string $statusVat, array $accountNumbers, string $requestId): void
    {
        $this->http->queueJson([
            'result' => [
                'subject' => [
                    'name' => 'Acme Sp. z o.o.',
                    'statusVat' => $statusVat,
                    'accountNumbers' => $accountNumbers,
                ],
                'requestId' => $requestId,
            ],
        ]);
    }
}
