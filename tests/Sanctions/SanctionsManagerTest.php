<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Sanctions;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Sanctions\SanctionsManager;
use Gawrys\Counterparty\Sanctions\SanctionsResult;
use Gawrys\Counterparty\Tests\Fixture\FakeSanctionsProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SanctionsManager::class)]
#[CoversClass(SanctionsResult::class)]
final class SanctionsManagerTest extends TestCase
{
    public function testScreenDelegatesToNamedProvider(): void
    {
        $manager = new SanctionsManager();
        $manager->extend('sanctions-network', static fn (): FakeSanctionsProvider => new FakeSanctionsProvider(hit: true));

        $result = $manager->screen('sanctions-network', new Counterparty('Blocked Entity', 'RU'));

        self::assertTrue($result->hasMatches());
        self::assertGreaterThan(0.9, $result->highestScore());
    }

    public function testClearResultHasNoMatches(): void
    {
        $manager = new SanctionsManager();
        $manager->extend('sanctions-network', static fn (): FakeSanctionsProvider => new FakeSanctionsProvider());

        $result = $manager->screen('sanctions-network', new Counterparty('Acme', 'PL'));

        self::assertFalse($result->hasMatches());
        self::assertSame(0.0, $result->highestScore());
    }
}
