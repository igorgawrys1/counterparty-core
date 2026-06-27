<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Vies;

interface ViesClient
{
    /**
     * @param string $countryCode ISO-3166-1 alpha-2 (VAT prefix), upper-cased
     * @param string $vatNumber national VAT number without the country prefix
     */
    public function validate(string $countryCode, string $vatNumber): ViesResult;
}
