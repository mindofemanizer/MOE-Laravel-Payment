<?php

declare(strict_types=1);

namespace MOE\Payment\Facades;

use Illuminate\Support\Facades\Facade;
use MOE\Payment\Services\PaymentService;

/**
 * @method static array charge(\MOE\Payment\Models\Payment $payment, ?string $gateway = null, array $options = [])
 * @method static array refund(\MOE\Payment\Models\Payment $payment, ?float $amount = null, ?string $gateway = null)
 * @method static array status(string $transactionId, string $gateway)
 * @method static array handleWebhook(string $gateway, array $payload)
 * @method static \MOE\Payment\Contracts\PaymentGatewayInterface gateway(?string $name = null)
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentService::class;
    }
}
