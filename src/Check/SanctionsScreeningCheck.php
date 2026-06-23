<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Check;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Report\CheckResult;
use Gawrys\Counterparty\Report\Source;
use Gawrys\Counterparty\Sanctions\SanctionsMatch;
use Gawrys\Counterparty\Sanctions\SanctionsProvider;
use Psr\Clock\ClockInterface;

/**
 * Screens the counterparty against a sanctions/PEP provider. A match is a hard,
 * deterministic FAIL — never softened, never left to the AI subsystem.
 */
final readonly class SanctionsScreeningCheck implements Check
{
    public function __construct(
        private SanctionsProvider $provider,
        private ClockInterface $clock,
    ) {
    }

    public function name(): string
    {
        return 'Sanctions Screening';
    }

    public function source(): string
    {
        return Source::SANCTIONS;
    }

    public function supports(Counterparty $counterparty): bool
    {
        return true;
    }

    public function run(Counterparty $counterparty): CheckResult
    {
        $now = $this->clock->now();
        $result = $this->provider->screen($counterparty);

        if (!$result->hasMatches()) {
            return CheckResult::pass(
                Source::SANCTIONS,
                $this->name(),
                'No sanctions matches found.',
                $now,
                ['matches' => [], 'sourceUrl' => $result->sourceUrl],
                $result->proofId,
            );
        }

        $matches = array_map(
            static fn (SanctionsMatch $match): array => [
                'name' => $match->name,
                'score' => $match->score,
                'list' => $match->listName,
                'url' => $match->sourceUrl,
            ],
            $result->matches,
        );

        return CheckResult::fail(
            Source::SANCTIONS,
            $this->name(),
            \sprintf('%d potential sanctions match(es) found.', \count($result->matches)),
            $now,
            ['matches' => $matches, 'sourceUrl' => $result->sourceUrl],
            $result->proofId,
        );
    }
}
