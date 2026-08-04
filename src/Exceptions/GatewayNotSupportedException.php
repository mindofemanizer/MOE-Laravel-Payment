<?php

declare(strict_types=1);

namespace Moe\Payment\Exceptions;

use InvalidArgumentException;

class GatewayNotSupportedException extends InvalidArgumentException
{
    public function __construct(string $gateway)
    {
        parent::__construct("Payment gateway [{$gateway}] is not supported.");
    }
}
