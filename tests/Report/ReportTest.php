<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Report;

use Gawrys\Counterparty\Enum\CheckStatus;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Report\VerificationReport;
use Gawrys\Counterparty\Testing\FrozenClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CheckResult::class)]
#[CoversClass(VerificationReport::class)]
final class ReportTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = (new FrozenClock())->now();
    }

    public function testNamedConstructorsSetStatus(): void
    {
        self::assertSame(CheckStatus::Pass, CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->now)->status);
        self::assertSame(CheckStatus::Fail, CheckResult::fail(Source::VIES, 'VIES', 'no', $this->now)->status);
        self::assertSame(CheckStatus::Warning, CheckResult::warning(Source::VIES, 'VIES', 'hm', $this->now)->status);
        self::assertSame(CheckStatus::Inconclusive, CheckResult::inconclusive(Source::VIES, 'VIES', 'gap', $this->now)->status);
    }

    public function testProofIdIsCarried(): void
    {
        $result = CheckResult::pass(Source::WHITE_LIST, 'White List', 'active', $this->now, ['x' => 1], 'PL/2024/abc');

        self::assertSame('PL/2024/abc', $result->proofId);
        self::assertSame(['x' => 1], $result->raw);
    }

    public function testReportAggregatesStatus(): void
    {
        $report = new VerificationReport(
            CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->now),
            CheckResult::fail(Source::SANCTIONS, 'Sanctions', 'hit', $this->now),
            CheckResult::inconclusive(Source::KRS, 'KRS', 'gap', $this->now),
        );

        self::assertCount(3, $report);
        self::assertTrue($report->hasAdverseFindings());
        self::assertTrue($report->hasInconclusive());
        self::assertSame(CheckStatus::Fail, $report->worstStatus());
        self::assertCount(1, $report->fromSource(Source::SANCTIONS));
    }

    public function testEmptyReport(): void
    {
        $report = new VerificationReport();

        self::assertTrue($report->isEmpty());
        self::assertSame(CheckStatus::Pass, $report->worstStatus());
        self::assertFalse($report->hasAdverseFindings());
    }

    public function testWithReturnsNewInstance(): void
    {
        $report = new VerificationReport();
        $extended = $report->with(CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->now));

        self::assertCount(0, $report);
        self::assertCount(1, $extended);
    }

    public function testIteration(): void
    {
        $report = new VerificationReport(CheckResult::pass(Source::VIES, 'VIES', 'ok', $this->now));

        $sources = [];
        foreach ($report as $result) {
            $sources[] = $result->source;
        }

        self::assertSame([Source::VIES], $sources);
    }
}
