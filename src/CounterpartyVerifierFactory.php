<?php

declare(strict_types=1);

namespace Gawrys\Counterparty;

use Gawrys\Counterparty\Adapter\Sanctions\SanctionsNetworkProvider;
use Gawrys\Counterparty\Adapter\Vies\HttpViesClient;
use Gawrys\Counterparty\Adapter\WhiteList\HttpWhiteListClient;
use Gawrys\Counterparty\Check\SanctionsScreeningCheck;
use Gawrys\Counterparty\Check\ViesCheck;
use Gawrys\Counterparty\Check\WhiteListCheck;
use Gawrys\Counterparty\Http\JsonHttpClient;
use Gawrys\Counterparty\Risk\RiskStrategy;
use Gawrys\Counterparty\Risk\RuleBasedRiskStrategy;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Convenience factory that assembles a ready-to-use {@see Verifier} with the bundled
 * reference checks (PL White List, EU VIES, sanctions.network) and the rule-based risk
 * strategy - so a framework-less consumer does not have to wire each check by hand.
 *
 * The Laravel/Symfony bridges perform the equivalent wiring through their container.
 */
final class CounterpartyVerifierFactory
{
    /**
     * Build a verifier from an explicit PSR-18 client. PSR-17 factories, the clock, the risk
     * strategy and the logger are optional; factories are auto-discovered when omitted and
     * the clock defaults to the system clock.
     */
    public static function create(
        ClientInterface $httpClient,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?RiskStrategy $riskStrategy = null,
        ?ClockInterface $clock = null,
        ?LoggerInterface $logger = null,
    ): Verifier {
        $http = new JsonHttpClient(
            $httpClient,
            $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory(),
        );

        return new Verifier(
            [
                new WhiteListCheck(new HttpWhiteListClient($http)),
                new ViesCheck(new HttpViesClient($http)),
                new SanctionsScreeningCheck(new SanctionsNetworkProvider($http)),
            ],
            $riskStrategy ?? RuleBasedRiskStrategy::withDefaultRules(),
            $clock,
            $logger,
        );
    }

    /**
     * Build a verifier by auto-discovering an installed PSR-18 client and PSR-17 factories
     * via php-http/discovery. Requires a discoverable implementation to be installed
     * (e.g. symfony/http-client + nyholm/psr7, or a Guzzle PSR-18 adapter).
     */
    public static function discover(
        ?RiskStrategy $riskStrategy = null,
        ?ClockInterface $clock = null,
        ?LoggerInterface $logger = null,
    ): Verifier {
        return self::create(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
            $riskStrategy,
            $clock,
            $logger,
        );
    }
}
