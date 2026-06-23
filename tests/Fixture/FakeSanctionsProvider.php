<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Tests\Fixture;

use Gawrys\Counterparty\Counterparty;
use Gawrys\Counterparty\Sanctions\SanctionsMatch;
use Gawrys\Counterparty\Sanctions\SanctionsProvider;
use Gawrys\Counterparty\Sanctions\SanctionsResult;

final class FakeSanctionsProvider implements SanctionsProvider
{
    public function __construct(private readonly bool $hit = false)
    {
    }

    public function screen(Counterparty $counterparty): SanctionsResult
    {
        if (!$this->hit) {
            return SanctionsResult::clear('https://sanctions.example.test', 'screen-1');
        }

        return new SanctionsResult(
            [new SanctionsMatch($counterparty->name, 0.92, 'EU Consolidated', 'https://sanctions.example.test/1')],
            'https://sanctions.example.test',
            'screen-1',
        );
    }
}
