<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Report;

/**
 * Canonical source identifiers used by reference checks and matched by risk rules.
 *
 * Third-party adapters are free to define their own identifiers; these are only the
 * ones the bundled reference adapters and default rules agree on.
 */
final class Source
{
    public const WHITE_LIST = 'pl.white_list';
    public const VIES = 'eu.vies';
    public const SANCTIONS = 'sanctions';
    public const KRS = 'pl.krs';
    public const CEIDG = 'pl.ceidg';
    public const REGON = 'pl.regon';
    public const CRBR = 'pl.crbr';

    private function __construct()
    {
    }
}
