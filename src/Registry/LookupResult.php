<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Registry;

/**
 * The contract every {@see RegistryDriver} returns. Carries the structured data plus an
 * optional due-diligence proof token and source URL for evidencing the lookup later.
 */
final readonly class LookupResult
{
    /**
     * @param array<string, mixed> $data structured, provider-specific payload
     */
    public function __construct(
        public bool $found,
        public array $data = [],
        public ?string $proofId = null,
        public ?string $sourceUrl = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function found(array $data, ?string $proofId = null, ?string $sourceUrl = null): self
    {
        return new self(true, $data, $proofId, $sourceUrl);
    }

    public static function notFound(?string $sourceUrl = null): self
    {
        return new self(false, [], null, $sourceUrl);
    }
}
