<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Risk;

use Gawrys\Counterparty\Enum\RiskLevel;
use Gawrys\Counterparty\Exception\InvalidEvidence;
use Gawrys\Counterparty\Risk\Evidence;
use Gawrys\Counterparty\Risk\RiskAssessment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Evidence::class)]
#[CoversClass(RiskAssessment::class)]
final class EvidenceTest extends TestCase
{
    public function testGroundedEvidence(): void
    {
        $evidence = Evidence::grounded('Listed on OFAC SDN', 'https://example.test/sdn', 0.95);

        self::assertTrue($evidence->isGrounded());
        self::assertSame('https://example.test/sdn', $evidence->sourceUrl);
    }

    public function testUngroundedEvidence(): void
    {
        $evidence = Evidence::ungrounded('A claim with no source');

        self::assertFalse($evidence->isGrounded());
        self::assertNull($evidence->sourceUrl);
    }

    public function testRejectsEmptyClaim(): void
    {
        $this->expectException(InvalidEvidence::class);

        Evidence::ungrounded('   ');
    }

    public function testRejectsConfidenceOutOfRange(): void
    {
        $this->expectException(InvalidEvidence::class);

        new Evidence('claim', null, 1.5);
    }

    public function testAssessmentFiltersGroundedEvidence(): void
    {
        $assessment = new RiskAssessment(
            RiskLevel::Medium,
            0.4,
            'summary',
            true,
            [
                Evidence::grounded('grounded', 'https://example.test', 0.8),
                Evidence::ungrounded('ungrounded'),
            ],
        );

        self::assertTrue($assessment->requiresHumanReview());
        self::assertCount(2, $assessment->evidence);
        self::assertCount(1, $assessment->groundedEvidence());
    }
}
