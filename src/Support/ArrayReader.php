<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Support;

/**
 * Type-safe extraction from decoded JSON (`array<string, mixed>`), so reference adapters
 * stay strict and readable without scattering `is_*` checks. Values are genuinely `mixed`
 * (they come off the wire), hence the explicit `@var mixed` acknowledgements below.
 */
final readonly class ArrayReader
{
    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function of(array $data): self
    {
        return new self($data);
    }

    public function string(string $key): ?string
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;

        return \is_string($value) ? $value : null;
    }

    public function bool(string $key): ?bool
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;

        return \is_bool($value) ? $value : null;
    }

    public function float(string $key): ?float
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;
        if (\is_int($value) || \is_float($value)) {
            return (float) $value;
        }

        return \is_string($value) && is_numeric($value) ? (float) $value : null;
    }

    public function nested(string $key): self
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;

        return new self(\is_array($value) ? $value : []);
    }

    public function has(string $key): bool
    {
        return ($this->data[$key] ?? null) !== null;
    }

    /**
     * @return list<string>
     */
    public function stringList(string $key): array
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;
        if (!\is_array($value)) {
            return [];
        }

        $out = [];
        /** @var mixed $item */
        foreach ($value as $item) {
            if (\is_string($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * Each array-valued element of $key, wrapped as its own reader.
     *
     * @return list<self>
     */
    public function each(string $key): array
    {
        /** @var mixed $value */
        $value = $this->data[$key] ?? null;
        if (!\is_array($value)) {
            return [];
        }

        $out = [];
        /** @var mixed $item */
        foreach ($value as $item) {
            if (\is_array($item)) {
                $out[] = new self($item);
            }
        }

        return $out;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
