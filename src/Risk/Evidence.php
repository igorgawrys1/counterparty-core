<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

use Gawrys\Counterparty\Exception\InvalidEvidence;

/**
 * A single grounded claim backing a {@see RiskAssessment}.
 *
 * Evidence without a source URL is "ungrounded": the risk engine and the AI subsystem
 * must treat ungrounded claims as inconclusive and never as a confirmed fact.
 */
final readonly class Evidence
{
    /**
     * @param float $confidence value in [0.0, 1.0]
     */
    public function __construct(
        public string $claim,
        public ?string $sourceUrl,
        public float $confidence,
    ) {
        if (trim($claim) === '') {
            throw InvalidEvidence::emptyClaim();
        }

        if ($confidence < 0.0 || $confidence > 1.0) {
            throw InvalidEvidence::confidenceOutOfRange($confidence);
        }
    }

    public static function grounded(string $claim, string $sourceUrl, float $confidence): self
    {
        return new self($claim, $sourceUrl, $confidence);
    }

    public static function ungrounded(string $claim, float $confidence = 0.0): self
    {
        return new self($claim, null, $confidence);
    }

    public function isGrounded(): bool
    {
        return $this->sourceUrl !== null;
    }
}
