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
 * Reference sanctions screening adapter targeting sanctions.network - a free, public
 * PostgREST API over consolidated OFAC/EU sanctions data. Matching is performed
 * server-side by the `search_sanctions(name)` function; this is the default provider
 * precisely because it carries no restrictive licence.
 *
 * @see https://api.sanctions.network/
 */
final readonly class SanctionsNetworkProvider implements SanctionsProvider
{
    private const DEFAULT_BASE_URI = 'https://api.sanctions.network';

    private string $baseUri;

    public function __construct(
        private JsonHttpClient $http,
        string $baseUri = self::DEFAULT_BASE_URI,
    ) {
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function screen(Counterparty $counterparty): SanctionsResult
    {
        $uri = \sprintf('%s/rpc/search_sanctions?%s', $this->baseUri, http_build_query(['name' => $counterparty->name]));

        $matches = [];
        /** @var mixed $row */
        foreach ($this->http->getJson($uri) as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $reader = ArrayReader::of($row);
            $names = $reader->stringList('names');

            $matches[] = new SanctionsMatch(
                name: $names[0] ?? $counterparty->name,
                score: 1.0, // the API matches server-side; a returned row is a match
                listName: $reader->string('source'),
                sourceUrl: null,
                raw: $row,
            );
        }

        return new SanctionsResult($matches, $uri);
    }
}
