<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Report;

use Gawrys\Counterparty\Enum\CheckStatus;

/**
 * Immutable collection of {@see CheckResult} - the body of hard facts a risk strategy
 * consumes as ground truth.
 *
 * @implements \IteratorAggregate<int, CheckResult>
 */
final readonly class VerificationReport implements \IteratorAggregate, \Countable
{
    /** @var list<CheckResult> */
    private array $results;

    public function __construct(CheckResult ...$results)
    {
        $this->results = array_values($results);
    }

    public function with(CheckResult $result): self
    {
        return new self(...[...$this->results, $result]);
    }

    /**
     * @return list<CheckResult>
     */
    public function results(): array
    {
        return $this->results;
    }

    /**
     * @return list<CheckResult>
     */
    public function fromSource(string $source): array
    {
        return array_values(array_filter(
            $this->results,
            static fn (CheckResult $r): bool => $r->source === $source,
        ));
    }

    public function hasAdverseFindings(): bool
    {
        foreach ($this->results as $result) {
            if ($result->isAdverse()) {
                return true;
            }
        }

        return false;
    }

    public function hasInconclusive(): bool
    {
        foreach ($this->results as $result) {
            if (!$result->status->isConclusive()) {
                return true;
            }
        }

        return false;
    }

    public function worstStatus(): CheckStatus
    {
        $worst = CheckStatus::Pass;
        foreach ($this->results as $result) {
            if ($result->status->severity() > $worst->severity()) {
                $worst = $result->status;
            }
        }

        return $worst;
    }

    public function isEmpty(): bool
    {
        return $this->results === [];
    }

    public function count(): int
    {
        return \count($this->results);
    }

    /**
     * @return \Traversable<int, CheckResult>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->results);
    }
}
