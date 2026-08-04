<?php

declare(strict_types=1);

namespace Moe\Payment\Contracts;

use Moe\Payment\Models\Payment;

interface PaymentGatewayInterface
{
    public function charge(Payment $payment, array $options = []): array;

    public function refund(Payment $payment, ?float $amount = null): array;

    public function status(string $transactionId): array;

    public function webhook(array $payload): array;

    public function name(): string;
}
