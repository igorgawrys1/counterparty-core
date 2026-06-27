<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Risk;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Enum\RiskLevel;
use Gawrys\Counterparty\Report\VerificationReport;

/**
 * Deterministic, AI-free default strategy.
 *
 * Aggregates the signals emitted by a set of composable {@see RiskRule}s. The overall
 * score is the strongest single signal (escalation, not summation, so unrelated minor
 * findings cannot inflate one another). Human review is demanded on any adverse signal,
 * on any inconclusive check, or when the score reaches the configured threshold.
 */
final readonly class RuleBasedRiskStrategy implements RiskStrategy
{
    /** @var list<RiskRule> */
    private array $rules;

    /**
     * @param iterable<RiskRule> $rules
     * @param float $reviewThreshold score (inclusive) at or above which human review is required
     */
    public function __construct(
        iterable $rules,
        private float $reviewThreshold = 0.5,
    ) {
        $this->rules = \is_array($rules) ? array_values($rules) : iterator_to_array($rules, false);
    }

    /**
     * Convenience factory wiring the bundled default rule set.
     */
    public static function withDefaultRules(float $reviewThreshold = 0.5): self
    {
        return new self([
            new Rule\SanctionsHitRule(),
            new Rule\VatStatusRule(),
            new Rule\BankAccountMismatchRule(),
            new Rule\InconclusiveCoverageRule(),
        ], $reviewThreshold);
    }

    public function assess(Counterparty $counterparty, VerificationReport $report): RiskAssessment
    {
        $context = new RiskContext($counterparty, $report);

        $score = 0.0;
        $anyAdverse = false;
        /** @var list<Evidence> $evidence */
        $evidence = [];
        /** @var list<string> $codes */
        $codes = [];

        foreach ($this->rules as $rule) {
            foreach ($rule->evaluate($context) as $signal) {
                $score = max($score, $signal->weight);
                $anyAdverse = $anyAdverse || $signal->adverse;
                $codes[] = $signal->code;
                if ($signal->evidence !== null) {
                    $evidence[] = $signal->evidence;
                }
            }
        }

        $requiresReview = $anyAdverse
            || $report->hasInconclusive()
            || $score >= $this->reviewThreshold;

        return new RiskAssessment(
            RiskLevel::fromScore($score),
            $score,
            $this->summarise($codes, $anyAdverse, $report),
            $requiresReview,
            $evidence,
        );
    }

    /**
     * @param list<string> $codes
     */
    private function summarise(array $codes, bool $anyAdverse, VerificationReport $report): string
    {
        if ($codes === []) {
            return $report->isEmpty()
                ? 'No checks were run; risk could not be assessed.'
                : 'No risk signals were raised by the configured rules.';
        }

        $prefix = $anyAdverse ? 'Adverse findings present' : 'Risk signals raised';

        return \sprintf('%s: %s.', $prefix, implode(', ', array_unique($codes)));
    }
}
