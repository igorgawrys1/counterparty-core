<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Risk\RuleBasedRiskStrategy;
use Gawrys\Counterparty\Testing\FrozenClock;
use Gawrys\Counterparty\Tests\Fixture\StaticCheck;
use Gawrys\Counterparty\Tests\Fixture\ThrowingCheck;
use Gawrys\Counterparty\Verifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Verifier::class)]
#[CoversClass(\Gawrys\Counterparty\VerificationOutcome::class)]
final class VerifierTest extends TestCase
{
    private Counterparty $counterparty;
    private FrozenClock $clock;

    protected function setUp(): void
    {
        $this->counterparty = new Counterparty('Acme', 'PL', nip: '1234567890');
        $this->clock = new FrozenClock();
    }

    public function testItRunsSupportedChecksAndAssessesRisk(): void
    {
        $verifier = new Verifier(
            [
                new StaticCheck(CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->clock->now())),
                new StaticCheck(CheckResult::fail(Source::SANCTIONS, 'Sanctions', 'hit', $this->clock->now())),
            ],
            RuleBasedRiskStrategy::withDefaultRules(),
            $this->clock,
        );

        $outcome = $verifier->verify($this->counterparty);

        self::assertCount(2, $outcome->report);
        self::assertTrue($outcome->hasAdverseFindings());
        self::assertTrue($outcome->requiresHumanReview());
        self::assertSame($this->counterparty, $outcome->counterparty);
    }

    public function testItSkipsUnsupportedChecks(): void
    {
        $verifier = new Verifier(
            [
                new StaticCheck(CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->clock->now()), supported: false),
            ],
            RuleBasedRiskStrategy::withDefaultRules(),
            $this->clock,
        );

        $outcome = $verifier->verify($this->counterparty);

        self::assertCount(0, $outcome->report);
    }

    public function testItConvertsUnexpectedFailuresToInconclusive(): void
    {
        $verifier = new Verifier(
            [new ThrowingCheck()],
            RuleBasedRiskStrategy::withDefaultRules(),
            $this->clock,
        );

        $outcome = $verifier->verify($this->counterparty);

        self::assertCount(1, $outcome->report);
        self::assertSame(CheckStatus::Inconclusive, $outcome->report->results()[0]->status);
        self::assertTrue($outcome->requiresHumanReview());
    }
}
