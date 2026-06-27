<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Enum;

use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Enum\RegistryCapability;
use Gawrys\Counterparty\Enum\RiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CheckStatus::class)]
#[CoversClass(RiskLevel::class)]
#[CoversClass(RegistryCapability::class)]
final class EnumTest extends TestCase
{
    public function testOnlyFailIsAdverse(): void
    {
        self::assertTrue(CheckStatus::Fail->isAdverse());
        self::assertFalse(CheckStatus::Warning->isAdverse());
        self::assertFalse(CheckStatus::Pass->isAdverse());
    }

    public function testOnlyInconclusiveIsNotConclusive(): void
    {
        self::assertFalse(CheckStatus::Inconclusive->isConclusive());
        self::assertTrue(CheckStatus::Pass->isConclusive());
    }

    public function testSeverityOrdering(): void
    {
        self::assertGreaterThan(CheckStatus::Warning->severity(), CheckStatus::Fail->severity());
        self::assertGreaterThan(CheckStatus::Inconclusive->severity(), CheckStatus::Warning->severity());
        self::assertGreaterThan(CheckStatus::Pass->severity(), CheckStatus::Inconclusive->severity());
    }

    /**
     * @return iterable<string, array{float, RiskLevel}>
     */
    public static function scoreProvider(): iterable
    {
        yield 'zero is low' => [0.0, RiskLevel::Low];
        yield 'just below medium' => [0.24, RiskLevel::Low];
        yield 'medium boundary' => [0.25, RiskLevel::Medium];
        yield 'high boundary' => [0.5, RiskLevel::High];
        yield 'critical boundary' => [0.8, RiskLevel::Critical];
        yield 'max' => [1.0, RiskLevel::Critical];
    }

    #[DataProvider('scoreProvider')]
    public function testRiskLevelFromScore(float $score, RiskLevel $expected): void
    {
        self::assertSame($expected, RiskLevel::fromScore($score));
    }

    public function testRiskLevelOrdering(): void
    {
        self::assertTrue(RiskLevel::Critical->isAtLeast(RiskLevel::High));
        self::assertFalse(RiskLevel::Low->isAtLeast(RiskLevel::Medium));
    }

    public function testCapabilitiesHaveLabels(): void
    {
        foreach (RegistryCapability::cases() as $capability) {
            self::assertNotSame('', $capability->label());
        }
    }
}
