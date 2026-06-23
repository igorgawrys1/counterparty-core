<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Exception;

final class HttpRequestFailed extends \RuntimeException implements CounterpartyException
{
    public static function transport(string $uri, \Throwable $previous): self
    {
        return new self(\sprintf('HTTP transport error for "%s": %s', $uri, $previous->getMessage()), 0, $previous);
    }

    public static function unexpectedStatus(string $uri, int $status): self
    {
        return new self(\sprintf('Unexpected HTTP status %d for "%s".', $status, $uri));
    }

    public static function invalidJson(string $uri, ?\Throwable $previous = null): self
    {
        return new self(\sprintf('Response from "%s" was not valid JSON.', $uri), 0, $previous);
    }
}
