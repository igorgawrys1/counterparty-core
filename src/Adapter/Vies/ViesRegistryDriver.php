<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Vies;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\AbstractRegistryDriver;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\LookupResult;

/**
 * Exposes EU VIES validation on the capability-routed registry axis for every EU member
 * state, in addition to the dedicated {@see \Gawrys\Counterparty\Check\ViesCheck}.
 */
final readonly class ViesRegistryDriver extends AbstractRegistryDriver
{
    // ISO-3166-1 alpha-2 country codes (routing is by Counterparty::$country). Note that
    // Greece's VAT prefix in VIES is "EL" while its ISO country code is "GR" - we route by
    // the ISO code and the VAT prefix is taken from the counterparty's EU VAT number.
    private const EU_MEMBER_STATES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
        'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
    ];

    public function __construct(private ViesClient $client)
    {
    }

    public function capabilities(): array
    {
        return [RegistryCapability::EuVatValidation];
    }

    public function countries(): array
    {
        return self::EU_MEMBER_STATES;
    }

    public function lookup(LookupRequest $request): LookupResult
    {
        $euVat = $request->counterparty->euVat;
        if ($euVat === null || \strlen($euVat) <= 2) {
            return LookupResult::notFound('https://ec.europa.eu/taxation_customs/vies/');
        }

        $result = $this->client->validate(substr($euVat, 0, 2), substr($euVat, 2));
        $url = 'https://ec.europa.eu/taxation_customs/vies/';

        if (!$result->valid) {
            return LookupResult::notFound($url);
        }

        return LookupResult::found(
            ['name' => $result->name, 'address' => $result->address, 'valid' => true],
            null,
            $url,
        );
    }
}
