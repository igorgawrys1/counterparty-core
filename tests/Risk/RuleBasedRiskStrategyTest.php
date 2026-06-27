<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Risk;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RiskLevel;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Report\VerificationReport;
use Gawrys\Counterparty\Risk\RiskContext;
use Gawrys\Counterparty\Risk\RiskRule;
use Gawrys\Counterparty\Risk\RiskSignal;
use Gawrys\Counterparty\Risk\Rule\BankAccountMismatchRule;
use Gawrys\Counterparty\Risk\Rule\SanctionsHitRule;
use Gawrys\Counterparty\Risk\Rule\VatStatusRule;
use Gawrys\Counterparty\Risk\RuleBasedRiskStrategy;
use Gawrys\Counterparty\Testing\FrozenClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleBasedRiskStrategy::class)]
#[CoversClass(SanctionsHitRule::class)]
#[CoversClass(VatStatusRule::class)]
#[CoversClass(BankAccountMismatchRule::class)]
#[CoversClass(RiskSignal::class)]
final class RuleBasedRiskStrategyTest extends TestCase
{
    private Counterparty $counterparty;
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->counterparty = new Counterparty('Acme', 'PL', nip: '1234567890');
        $this->now = (new FrozenClock())->now();
    }

    public function testSanctionsHitIsCriticalAndAdverse(): void
    {
        $report = new VerificationReport(
            CheckResult::fail(Source::SANCTIONS, 'Sanctions', 'Match on SDN list', $this->now),
        );

        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, $report);

        self::assertSame(RiskLevel::Critical, $assessment->level);
        self::assertSame(1.0, $assessment->score);
        self::assertTrue($assessment->requiresHumanReview());
    }

    public function testCleanReportIsLowRiskWithoutReview(): void
    {
        $report = new VerificationReport(
            CheckResult::pass(Source::WHITE_LIST, 'White List', 'Active VAT payer', $this->now, ['bankAccountAssigned' => true]),
            CheckResult::pass(Source::VIES, 'VIES', 'Valid EU VAT', $this->now),
        );

        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, $report);

        self::assertSame(RiskLevel::Low, $assessment->level);
        self::assertFalse($assessment->requiresHumanReview());
    }

    public function testInactiveVatRaisesScore(): void
    {
        $report = new VerificationReport(
            CheckResult::fail(Source::WHITE_LIST, 'White List', 'Not an active VAT payer', $this->now),
        );

        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, $report);

        self::assertSame(RiskLevel::High, $assessment->level);
        self::assertTrue($assessment->requiresHumanReview());
    }

    public function testUnassignedBankAccountIsAdverse(): void
    {
        $report = new VerificationReport(
            CheckResult::warning(Source::WHITE_LIST, 'White List', 'IBAN not assigned', $this->now, ['bankAccountAssigned' => false]),
        );

        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, $report);

        self::assertTrue($assessment->requiresHumanReview());
        self::assertTrue($assessment->level->isAtLeast(RiskLevel::High));
    }

    public function testInconclusiveForcesReview(): void
    {
        $report = new VerificationReport(
            CheckResult::inconclusive(Source::KRS, 'KRS', 'No driver for country', $this->now),
        );

        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, $report);

        self::assertTrue($assessment->requiresHumanReview());
        self::assertSame(RiskLevel::Low, $assessment->level);
    }

    public function testEmptyReportCannotBeAssessed(): void
    {
        $assessment = RuleBasedRiskStrategy::withDefaultRules()->assess($this->counterparty, new VerificationReport());

        self::assertSame(RiskLevel::Low, $assessment->level);
        self::assertStringContainsString('No checks', $assessment->summary);
    }

    public function testCustomRuleIsApplied(): void
    {
        $rule = new class () implements RiskRule {
            public function evaluate(RiskContext $context): iterable
            {
                yield new RiskSignal('custom.flag', 0.9, true);
            }
        };

        $assessment = (new RuleBasedRiskStrategy([$rule]))->assess($this->counterparty, new VerificationReport());

        self::assertSame(RiskLevel::Critical, $assessment->level);
        self::assertTrue($assessment->requiresHumanReview());
        self::assertStringContainsString('custom.flag', $assessment->summary);
    }

    public function testWeightIsClampedIntoRange(): void
    {
        $signal = new RiskSignal('x', 5.0, false);

        self::assertSame(1.0, $signal->weight);
    }
}
