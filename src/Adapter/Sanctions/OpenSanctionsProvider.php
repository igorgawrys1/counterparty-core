<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Sanctions;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Sanctions\SanctionsMatch;
use Gawrys\Counterparty\Sanctions\SanctionsProvider;
use Gawrys\Counterparty\Sanctions\SanctionsResult;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * Optional adapter for the OpenSanctions match API (or a self-hosted `yente` instance).
 *
 * LICENCE NOTE: OpenSanctions data is offered under a non-commercial licence; commercial
 * use of the hosted API requires a paid agreement with OpenSanctions. This adapter is NOT
 * the default for that reason - {@see SanctionsNetworkProvider} is. Ensure you are
 * licensed before using this in production.
 *
 * @see https://www.opensanctions.org/licensing/
 * @see https://api.opensanctions.org/
 */
final readonly class OpenSanctionsProvider implements SanctionsProvider
{
    private const DEFAULT_BASE_URI = 'https://api.opensanctions.org';

    private string $baseUri;

    public function __construct(
        private JsonHttpClient $http,
        private ?string $apiKey = null,
        string $baseUri = self::DEFAULT_BASE_URI,
        private string $dataset = 'sanctions',
        private float $threshold = 0.7,
    ) {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function screen(Counterparty $counterparty): SanctionsResult
    {
        $uri = \sprintf('%s/match/%s', $this->baseUri, rawurlencode($this->dataset));
        $headers = $this->apiKey !== null ? ['Authorization' => 'ApiKey ' . $this->apiKey] : [];

        $payload = [
            'queries' => [
                'q' => [
                    'schema' => 'LegalEntity',
                    'properties' => ['name' => [$counterparty->name]],
                ],
            ],
        ];

        $reader = ArrayReader::of($this->http->postJson($uri, $payload, $headers))
            ->nested('responses')
            ->nested('q');

        $matches = [];
        foreach ($reader->each('results') as $row) {
            $score = $row->float('score') ?? 0.0;
            if ($score < $this->threshold) {
                continue;
            }

            $matches[] = new SanctionsMatch(
                $row->string('caption') ?? $counterparty->name,
                $score,
                'OpenSanctions',
                \sprintf('%s/entities/%s', $this->baseUri, $row->string('id') ?? ''),
                $row->toArray(),
            );
        }

        return new SanctionsResult($matches, $uri);
    }
}
