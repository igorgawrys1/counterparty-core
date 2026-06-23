<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Adapter\Vies;

/**
 * Outcome of an EU VIES VAT-number validation.
 */
final readonly class ViesResult
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public bool $valid,
        public ?string $name,
        public ?string $address,
        public array $raw = [],
    ) {
    }
}
