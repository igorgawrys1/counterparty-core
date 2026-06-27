<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Registry;

use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Registry\AbstractRegistryDriver;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\LookupResult;
use Gawrys\Counterparty\Support\ArrayReader;

/**
 * Shared PSR-18 plumbing for the Polish registry drivers (KRS, CEIDG, REGON, CRBR), all
 * of which are NIP-keyed and serve PL only. Subclasses declare their capabilities, build
 * the request URI, and decide whether the response represents a hit.
 *
 * Adding another Polish registry is a new subclass plus a registration - nothing in the
 * core, the checks, or the bridges changes.
 */
abstract readonly class AbstractPolishRegistryDriver extends AbstractRegistryDriver
{
    protected string $baseUri;

    /**
     * @param array<string, string> $headers extra request headers (e.g. an API token)
     */
    public function __construct(
        protected JsonHttpClient $http,
        ?string $baseUri = null,
        protected array $headers = [],
    ) {
        $this->baseUri = rtrim($baseUri ?? $this->defaultBaseUri(), '/');
    }

    final public function countries(): array
    {
        return ['PL'];
    }

    final public function lookup(LookupRequest $request): LookupResult
    {
        $nip = $request->counterparty->nip;
        if ($nip === null) {
            return LookupResult::notFound($this->humanUri());
        }

        $uri = $this->endpoint($nip);
        $json = $this->http->getJson($uri, $this->headers);
        $reader = ArrayReader::of($json);

        if (!$this->isFound($reader)) {
            return LookupResult::notFound($uri);
        }

        return LookupResult::found($json, $this->proofId($reader, $nip), $uri);
    }

    abstract protected function defaultBaseUri(): string;

    /** A human-facing base URL recorded when no request could be made. */
    abstract protected function humanUri(): string;

    abstract protected function endpoint(string $nip): string;

    abstract protected function isFound(ArrayReader $reader): bool;

    protected function proofId(ArrayReader $reader, string $nip): ?string
    {
        return null;
    }
}
