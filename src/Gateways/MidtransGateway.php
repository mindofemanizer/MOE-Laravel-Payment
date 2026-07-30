<?php

declare(strict_types=1);

namespace MOE\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MOE\Payment\Contracts\PaymentGatewayInterface;
use MOE\Payment\Models\Payment;

class MidtransGateway implements PaymentGatewayInterface
{
    protected string $serverKey;
    protected string $baseUrl;

    public function __construct(
        protected array $config = [],
    ) {
        $this->serverKey = $config['server_key'] ?? '';
        $isProduction = $config['is_production'] ?? false;
        $this->baseUrl = $isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function charge(Payment $payment, array $options = []): array
    {
        $payload = array_merge([
            'transaction_details' => [
                'order_id' => $payment->invoice_number,
                'gross_amount' => (int) $payment->amount,
            ],
            'customer_details' => $options['customer'] ?? [],
            'item_details' => $options['items'] ?? [],
        ], $options['midtrans'] ?? []);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->post("{$this->baseUrl}/charge", $payload);

        $body = $response->json();

        if (! $response->successful()) {
            Log::error('[MOE Payment] Midtrans charge failed', $body);
        }

        return $body ?? [];
    }

    public function refund(Payment $payment, ?float $amount = null): array
    {
        $transactionId = $payment->transaction_id;
        $payload = $amount ? ['refund_amount' => (int) $amount] : [];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->post("{$this->baseUrl}/charge/{$transactionId}/refund", $payload);

        return $response->json() ?? [];
    }

    public function status(string $transactionId): array
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->get("{$this->baseUrl}/{$transactionId}/status");

        return $response->json() ?? [];
    }

    public function webhook(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        return [
            'transaction_id' => $payload['transaction_id'] ?? '',
            'order_id' => $orderId,
            'status' => $this->mapStatus($transactionStatus, $fraudStatus),
            'raw' => $payload,
        ];
    }

    public function name(): string
    {
        return 'midtrans';
    }

    protected function mapStatus(string $transactionStatus, string $fraudStatus): string
    {
        return match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => 'paid',
            $transactionStatus === 'settlement' => 'paid',
            $transactionStatus === 'pending' => 'pending',
            $transactionStatus === 'deny' || $transactionStatus === 'cancel' => 'failed',
            $transactionStatus === 'expire' => 'expired',
            $transactionStatus === 'refund' || $transactionStatus === 'partial_refund' => 'refunded',
            default => 'unknown',
        };
    }
}
