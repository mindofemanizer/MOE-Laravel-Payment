<?php

declare(strict_types=1);

namespace MOE\Payment\Services;

use Illuminate\Support\Facades\Log;
use MOE\Payment\Models\Payment;

class PaymentService
{
    protected array $config;

    public function __construct(
        protected GatewayManager $manager,
        array $config = [],
    ) {
        $this->config = $config;
    }

    public function charge(Payment $payment, ?string $gateway = null, array $options = []): array
    {
        $gatewayName = $gateway ?: $this->config['default_gateway'] ?? 'midtrans';
        $gatewayConfig = $this->config['gateways'][$gatewayName] ?? [];

        $gatewayInstance = $this->manager->get($gatewayName, $gatewayConfig);

        $result = $gatewayInstance->charge($payment, $options);

        $this->log($gatewayName, 'charge', $payment, $result);

        return $result;
    }

    public function refund(Payment $payment, ?float $amount = null, ?string $gateway = null): array
    {
        $gatewayName = $gateway ?: $payment->gateway;
        $gatewayConfig = $this->config['gateways'][$gatewayName] ?? [];

        $gatewayInstance = $this->manager->get($gatewayName, $gatewayConfig);

        $result = $gatewayInstance->refund($payment, $amount);

        $this->log($gatewayName, 'refund', $payment, $result);

        return $result;
    }

    public function status(string $transactionId, string $gateway): array
    {
        $gatewayConfig = $this->config['gateways'][$gateway] ?? [];
        $gatewayInstance = $this->manager->get($gateway, $gatewayConfig);

        return $gatewayInstance->status($transactionId);
    }

    public function handleWebhook(string $gateway, array $payload): array
    {
        $gatewayName = $gateway;
        $gatewayConfig = $this->config['gateways'][$gateway] ?? [];
        $gatewayInstance = $this->manager->get($gateway, $gatewayConfig);

        $result = $gatewayInstance->webhook($payload);

        $this->log($gatewayName, 'webhook', null, $result);

        return $result;
    }

    public function gateway(?string $name = null): \MOE\Payment\Contracts\PaymentGatewayInterface
    {
        $gatewayName = $name ?: $this->config['default_gateway'] ?? 'midtrans';
        $gatewayConfig = $this->config['gateways'][$gatewayName] ?? [];

        return $this->manager->get($gatewayName, $gatewayConfig);
    }

    protected function log(string $gateway, string $action, ?Payment $payment, array $result): void
    {
        if (! ($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        Log::info("[MOE Payment] {$gateway}.{$action}", [
            'payment_id' => $payment?->getKey(),
            'result' => $this->config['logging']['store_payload'] ?? true ? $result : 'truncated',
        ]);
    }
}
