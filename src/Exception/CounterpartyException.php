<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Exception;

/**
 * Marker interface implemented by every exception thrown by this library, so consumers
 * can catch the whole surface with a single catch block.
 */
interface CounterpartyException extends \Throwable
{
}
