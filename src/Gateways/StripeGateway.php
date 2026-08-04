<?php

declare(strict_types=1);

namespace Moe\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Moe\Payment\Contracts\PaymentGatewayInterface;
use Moe\Payment\Models\Payment;

class StripeGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(
        protected array $config = [],
    ) {
        $this->secretKey = $config['secret_key'] ?? '';
        $this->baseUrl = 'https://api.stripe.com/v1';
    }

    public function charge(Payment $payment, array $options = []): array
    {
        $payload = [
            'amount' => (int) ($payment->amount * 100),
            'currency' => strtolower($payment->currency ?? 'idr'),
            'description' => $options['description'] ?? '',
            'metadata' => [
                'invoice_number' => $payment->invoice_number,
                'payment_id' => $payment->getKey(),
            ],
        ];

        if ($source = $options['source'] ?? null) {
            $payload['source'] = $source;
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->post("{$this->baseUrl}/charges", $payload);

        $body = $response->json();

        if (! $response->successful()) {
            Log::error('[MOE Payment] Stripe charge failed', $body ?? []);
        }

        return $body ?? [];
    }

    public function refund(Payment $payment, ?float $amount = null): array
    {
        $payload = ['charge' => $payment->transaction_id];

        if ($amount) {
            $payload['amount'] = (int) ($amount * 100);
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->post("{$this->baseUrl}/refunds", $payload);

        return $response->json() ?? [];
    }

    public function status(string $transactionId): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/charges/{$transactionId}");

        return $response->json() ?? [];
    }

    public function webhook(array $payload): array
    {
        $type = $payload['type'] ?? '';
        $data = $payload['data']['object'] ?? [];

        return [
            'transaction_id' => $data['id'] ?? '',
            'order_id' => $data['metadata']['invoice_number'] ?? '',
            'status' => match ($type) {
                'charge.succeeded' => 'paid',
                'charge.failed' => 'failed',
                'charge.refunded' => 'refunded',
                'charge.expired' => 'expired',
                default => 'pending',
            },
            'raw' => $payload,
        ];
    }

    public function name(): string
    {
        return 'stripe';
    }
}
