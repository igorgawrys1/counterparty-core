<?php

declare(strict_types=1);

namespace Gawrys\Counterparty;

use Gawrys\Counterparty\Check\Check;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\VerificationReport;
use Gawrys\Counterparty\Risk\RiskStrategy;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Orchestrates verification: run the applicable checks into a {@see VerificationReport}
 * of hard facts, then hand that report to a {@see RiskStrategy} for an advisory
 * {@see Risk\RiskAssessment}.
 *
 * This library is a due-diligence AID. A passing outcome does not make the user AML
 * compliant; that responsibility remains with the user.
 */
final readonly class Verifier
{
    /** @var list<Check> */
    private array $checks;

    private LoggerInterface $logger;

    /**
     * @param iterable<Check> $checks
     */
    public function __construct(
        iterable $checks,
        private RiskStrategy $riskStrategy,
        private ClockInterface $clock,
        ?LoggerInterface $logger = null,
    ) {
        $this->checks = \is_array($checks) ? array_values($checks) : iterator_to_array($checks, false);
        $this->logger = $logger ?? new NullLogger();
    }

    public function verify(Counterparty $counterparty): VerificationOutcome
    {
        $results = [];

        foreach ($this->checks as $check) {
            if (!$check->supports($counterparty)) {
                continue;
            }

            $results[] = $this->runGuarded($check, $counterparty);
        }

        $report = new VerificationReport(...$results);
        $assessment = $this->riskStrategy->assess($counterparty, $report);

        return new VerificationOutcome($counterparty, $report, $assessment);
    }

    private function runGuarded(Check $check, Counterparty $counterparty): CheckResult
    {
        try {
            return $check->run($counterparty);
        } catch (\Throwable $e) {
            $this->logger->error('Counterparty check failed unexpectedly.', [
                'check' => $check->name(),
                'source' => $check->source(),
                'exception' => $e,
            ]);

            return CheckResult::inconclusive(
                $check->source(),
                $check->name(),
                'The check could not be completed due to an unexpected error.',
                $this->clock->now(),
            );
        }
    }
}
