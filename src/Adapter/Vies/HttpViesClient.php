<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Vies;

use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * PSR-18 client for the European Commission VIES REST API.
 *
 * @see https://ec.europa.eu/taxation_customs/vies/
 */
final readonly class HttpViesClient implements ViesClient
{
    private const DEFAULT_BASE_URI = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

    private string $baseUri;

    public function __construct(
        private JsonHttpClient $http,
        string $baseUri = self::DEFAULT_BASE_URI,
    ) {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function validate(string $countryCode, string $vatNumber): ViesResult
    {
        $uri = \sprintf(
            '%s/ms/%s/vat/%s',
            $this->baseUri,
            rawurlencode(strtoupper($countryCode)),
            rawurlencode($vatNumber),
        );

        $json = $this->http->getJson($uri);
        $payload = ArrayReader::of($json);

        return new ViesResult(
            $payload->bool('valid') ?? $payload->bool('isValid') ?? false,
            $payload->string('name'),
            $payload->string('address'),
            $json,
        );
    }
}
