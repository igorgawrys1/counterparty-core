<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\WhiteList;

use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * PSR-18 client for the Polish Ministry of Finance White List
 * ("wykaz podatników VAT"), default endpoint https://wl-api.mf.gov.pl.
 *
 * @see https://wl-api.mf.gov.pl/
 */
final readonly class HttpWhiteListClient implements WhiteListClient
{
    private const DEFAULT_BASE_URI = 'https://wl-api.mf.gov.pl';

    private string $baseUri;

    public function __construct(
        private JsonHttpClient $http,
        string $baseUri = self::DEFAULT_BASE_URI,
    ) {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function searchByNip(string $nip, \DateTimeImmutable $date): WhiteListResult
    {
        $uri = \sprintf(
            '%s/api/search/nip/%s?date=%s',
            $this->baseUri,
            rawurlencode($nip),
            $date->format('Y-m-d'),
        );

        $payload = $this->http->getJson($uri);
        $result = ArrayReader::of($payload)->nested('result');
        $subject = $result->nested('subject');

        if (!$result->has('subject')) {
            return new WhiteListResult(false, VatStatus::NotRegistered, null, [], $result->string('requestId'), null, $payload);
        }

        return new WhiteListResult(
            true,
            VatStatus::fromApi($subject->string('statusVat')),
            $subject->string('name'),
            $subject->stringList('accountNumbers'),
            $result->string('requestId'),
            $subject->string('krs'),
            $payload,
        );
    }
}
