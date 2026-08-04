<?php

declare(strict_types=1);

namespace Moe\Payment\Exceptions;

use RuntimeException;

class PaymentFailedException extends RuntimeException
{
    public function __construct(
        string $gateway,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            "Payment failed on gateway [{$gateway}]: {$message}",
            $code,
            $previous,
        );
    }
}
