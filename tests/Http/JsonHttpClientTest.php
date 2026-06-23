<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Http;

use Gawrys\Counterparty\Exception\HttpRequestFailed;
use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Tests\Fixture\MockHttp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonHttpClient::class)]
#[CoversClass(HttpRequestFailed::class)]
final class JsonHttpClientTest extends TestCase
{
    public function testGetJsonDecodesObject(): void
    {
        $http = new MockHttp();
        $http->queueJson(['a' => 1, 'b' => ['c' => 2]]);

        $data = $http->jsonClient()->getJson('https://example.test/x');

        self::assertSame(1, $data['a']);
    }

    public function testPostJsonSendsBody(): void
    {
        $http = new MockHttp();
        $http->queueJson(['ok' => true]);

        $data = $http->jsonClient()->postJson('https://example.test/x', ['q' => 'v']);

        self::assertTrue($data['ok']);
        $requests = $http->client->getRequests();
        self::assertCount(1, $requests);
        self::assertSame('{"q":"v"}', (string) $requests[0]->getBody());
    }

    public function testNon2xxThrows(): void
    {
        $http = new MockHttp();
        $http->queueJson(['error' => 'nope'], 500);

        $this->expectException(HttpRequestFailed::class);
        $http->jsonClient()->getJson('https://example.test/x');
    }

    public function testInvalidJsonThrows(): void
    {
        $http = new MockHttp();
        $http->queueRaw('<<not json>>');

        $this->expectException(HttpRequestFailed::class);
        $http->jsonClient()->getJson('https://example.test/x');
    }

    public function testEmptyBodyIsTreatedAsNoContent(): void
    {
        $http = new MockHttp();
        $http->queueRaw('', 204);

        self::assertSame([], $http->jsonClient()->getJson('https://example.test/x'));
    }

    public function testNonObjectJsonThrows(): void
    {
        $http = new MockHttp();
        $http->queueRaw('42');

        $this->expectException(HttpRequestFailed::class);
        $http->jsonClient()->getJson('https://example.test/x');
    }
}
