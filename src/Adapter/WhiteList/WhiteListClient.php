<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\WhiteList;

/**
 * Low-level port for the PL White List API. Implemented by {@see HttpWhiteListClient}
 * (PSR-18); applications may swap in their own (e.g. a cached or rate-limited variant).
 */
interface WhiteListClient
{
    public function searchByNip(string $nip, \DateTimeImmutable $date): WhiteListResult;
}
