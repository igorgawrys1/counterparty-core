<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Testing;

use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Registry\LookupRequest;
use Gawrys\Counterparty\Registry\RegistryDriver;
use PHPUnit\Framework\TestCase;

/**
 * Reusable contract every {@see RegistryDriver} must satisfy — the mechanism that keeps
 * the library extensible in practice. Third-party driver authors extend this case and
 * implement the two factory hooks; their driver should be wired to a mocked HTTP client
 * so the contract runs without live network access.
 *
 * @api
 */
abstract class RegistryDriverContractTestCase extends TestCase
{
    abstract protected function createDriver(): RegistryDriver;

    /**
     * A request the driver under test genuinely supports, backed by deterministic data.
     */
    abstract protected function supportedRequest(): LookupRequest;

    final public function testDeclaresAtLeastOneCapability(): void
    {
        self::assertNotEmpty($this->createDriver()->capabilities());
    }

    final public function testCapabilitiesAreUnique(): void
    {
        $capabilities = $this->createDriver()->capabilities();
        $distinct = array_unique(array_map(static fn (RegistryCapability $c): string => $c->value, $capabilities));

        self::assertCount(
            \count($capabilities),
            $distinct,
            'A driver must not declare the same capability twice.',
        );
    }

    final public function testServesAtLeastOneCountry(): void
    {
        self::assertNotEmpty($this->createDriver()->countries());
    }

    final public function testCountriesAreValidUpperCasedIsoAlpha2(): void
    {
        foreach ($this->createDriver()->countries() as $country) {
            self::assertMatchesRegularExpression(
                '/^[A-Z]{2}$/',
                $country,
                'Countries must be upper-cased ISO-3166-1 alpha-2 codes.',
            );
        }
    }

    final public function testSupportsEveryDeclaredCountryAndCapability(): void
    {
        $driver = $this->createDriver();

        foreach ($driver->countries() as $country) {
            foreach ($driver->capabilities() as $capability) {
                self::assertTrue(
                    $driver->supports($country, $capability),
                    \sprintf('Driver must support its declared pair (%s, %s).', $country, $capability->value),
                );
            }
        }
    }

    final public function testDoesNotSupportAnUnservedCountry(): void
    {
        $driver = $this->createDriver();
        $capability = $driver->capabilities()[0];

        // "ZZ" is permanently user-assigned in ISO-3166-1 and served by no real registry.
        self::assertFalse($driver->supports('ZZ', $capability));
    }

    final public function testDoesNotSupportAnUndeclaredCapability(): void
    {
        $driver = $this->createDriver();
        $declared = $driver->capabilities();
        $country = $driver->countries()[0];

        $undeclared = array_values(array_filter(
            RegistryCapability::cases(),
            static fn (RegistryCapability $c): bool => !\in_array($c, $declared, true),
        ));

        if ($undeclared === []) {
            self::markTestSkipped('Driver declares every capability; nothing left to assert.');
        }

        self::assertFalse($driver->supports($country, $undeclared[0]));
    }

    final public function testSupportedRequestIsActuallySupported(): void
    {
        $driver = $this->createDriver();
        $request = $this->supportedRequest();

        self::assertTrue(
            $driver->supports($request->counterparty->country, $request->capability),
            'supportedRequest() must return a request the driver supports.',
        );
    }

    final public function testLookupReturnsAWellFormedResult(): void
    {
        $result = $this->createDriver()->lookup($this->supportedRequest());

        if (!$result->found) {
            self::assertSame([], $result->data, 'A not-found result must carry no data.');
            self::assertNull($result->proofId, 'A not-found result must carry no proof identifier.');

            return;
        }

        self::assertNotSame([], $result->data, 'A found result should expose the registry data.');
    }
}
