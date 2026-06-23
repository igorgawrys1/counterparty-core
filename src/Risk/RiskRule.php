<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

/**
 * A composable scoring rule. Applications add their own rules to
 * {@see RuleBasedRiskStrategy} without subclassing it, or implement {@see RiskStrategy}
 * directly for a fully bespoke scoring model.
 */
interface RiskRule
{
    /**
     * Inspect the hard facts and emit zero or more signals. Rules must be pure: no I/O,
     * no clock, no randomness — given the same context they return the same signals.
     *
     * @return iterable<RiskSignal>
     */
    public function evaluate(RiskContext $context): iterable;
}
