<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

/**
 * A single contribution emitted by a {@see RiskRule}.
 *
 * The strategy aggregates signals into a score and decides whether human review is
 * required. A signal optionally carries the {@see Evidence} that justifies it.
 */
final readonly class RiskSignal
{
    /** Normalised contribution in [0.0, 1.0]. */
    public float $weight;

    /**
     * @param string $code stable machine identifier, e.g. "sanctions.hit"
     * @param float $weight contribution in [0.0, 1.0]; values outside the range are clamped
     * @param bool $adverse whether this signal is a hard negative finding
     */
    public function __construct(
        public string $code,
        float $weight,
        public bool $adverse,
        public ?Evidence $evidence = null,
    ) {
        $this->weight = max(0.0, min(1.0, $weight));
    }
}
