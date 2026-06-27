<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Registry;

use Gawrys\Counterparty\Adapter\WhiteList\WhiteListClient;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Registry\AbstractRegistryDriver;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\LookupResult;
use Psr\Clock\ClockInterface;

/**
 * National Court Register (Krajowy Rejestr Sądowy) driver for legal entities.
 *
 * The Ministry of Justice API is keyed by KRS number, not NIP, so this driver first
 * resolves NIP → KRS via the (public) White List, then fetches the current extract
 * ("odpis aktualny"). An entity with no KRS (e.g. a sole proprietor) yields not-found.
 *
 * @see https://api-krs.ms.gov.pl/
 */
final readonly class KrsRegistryDriver extends AbstractRegistryDriver
{
    private const DEFAULT_BASE_URI = 'https://api-krs.ms.gov.pl';

    private string $baseUri;

    public function __construct(
        private JsonHttpClient $http,
        private WhiteListClient $whiteList,
        private ClockInterface $clock,
        ?string $baseUri = null,
    ) {
        $this->baseUri = rtrim($baseUri ?? self::DEFAULT_BASE_URI, '/');
    }

    public function capabilities(): array
    {
        return [RegistryCapability::LegalEntityData, RegistryCapability::BusinessRegistration];
    }

    public function countries(): array
    {
        return ['PL'];
    }

    public function lookup(LookupRequest $request): LookupResult
    {
        $nip = $request->counterparty->nip;
        if ($nip === null) {
            return LookupResult::notFound('https://wyszukiwarka-krs.ms.gov.pl/');
        }

        $krs = $this->whiteList->searchByNip($nip, $this->clock->now())->krs;
        if ($krs === null || $krs === '') {
            return LookupResult::notFound('https://wyszukiwarka-krs.ms.gov.pl/');
        }

        $uri = \sprintf('%s/api/krs/OdpisAktualny/%s?rejestr=P&format=json', $this->baseUri, rawurlencode($krs));
        $json = $this->http->getJson($uri);

        return LookupResult::found($json, $krs, $uri);
    }
}
