<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Http;

use Gawrys\Counterparty\Exception\HttpRequestFailed;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Thin JSON helper over PSR-18 + PSR-17. Reference adapters use this instead of binding
 * to a concrete HTTP client, keeping the core framework- and vendor-agnostic.
 */
final readonly class JsonHttpClient
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<array-key, mixed>
     */
    public function getJson(string $uri, array $headers = []): array
    {
        return $this->send($this->createRequest('GET', $uri, $headers));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     *
     * @return array<array-key, mixed>
     */
    public function postJson(string $uri, array $payload, array $headers = []): array
    {
        $request = $this->createRequest('POST', $uri, ['Content-Type' => 'application/json'] + $headers)
            ->withBody($this->streamFactory->createStream($this->encode($uri, $payload)));

        return $this->send($request);
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $method, string $uri, array $headers): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function send(RequestInterface $request): array
    {
        $uri = (string) $request->getUri();

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw HttpRequestFailed::transport($uri, $e);
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw HttpRequestFailed::unexpectedStatus($uri, $status);
        }

        $body = (string) $response->getBody();
        if (trim($body) === '') {
            // A 2xx with no content (e.g. HTTP 204) is "no data", not a malformed response.
            return [];
        }

        return $this->decode($uri, $body);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(string $uri, array $payload): string
    {
        try {
            return json_encode($payload, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw HttpRequestFailed::invalidJson($uri, $e);
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decode(string $uri, string $body): array
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw HttpRequestFailed::invalidJson($uri, $e);
        }

        if (!\is_array($decoded)) {
            throw HttpRequestFailed::invalidJson($uri);
        }

        /** @var array<array-key, mixed> $decoded */
        return $decoded;
    }
}
