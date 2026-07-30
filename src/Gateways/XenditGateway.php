<?php

declare(strict_types=1);

namespace MOE\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MOE\Payment\Contracts\PaymentGatewayInterface;
use MOE\Payment\Models\Payment;

class XenditGateway implements PaymentGatewayInterface
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(
        protected array $config = [],
    ) {
        $this->secretKey = $config['secret_key'] ?? '';
        $this->baseUrl = 'https://api.xendit.co';
    }

    public function charge(Payment $payment, array $options = []): array
    {
        $payload = array_merge([
            'external_id' => $payment->invoice_number,
            'amount' => (int) $payment->amount,
            'description' => $options['description'] ?? '',
            'customer' => $options['customer'] ?? [],
            'items' => $options['items'] ?? [],
        ], $options['xendit'] ?? []);

        $response = Http::withBasicAuth($this->secretKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/v2/invoices", $payload);

        $body = $response->json();

        if (! $response->successful()) {
            Log::error('[MOE Payment] Xendit charge failed', $body ?? []);
        }

        return $body ?? [];
    }

    public function refund(Payment $payment, ?float $amount = null): array
    {
        $invoiceId = $payment->transaction_id;

        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/v2/invoices/{$invoiceId}/refunds");

        return $response->json() ?? [];
    }

    public function status(string $transactionId): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/v2/invoices/{$transactionId}");

        return $response->json() ?? [];
    }

    public function webhook(array $payload): array
    {
        $status = $payload['status'] ?? '';

        return [
            'transaction_id' => $payload['id'] ?? '',
            'order_id' => $payload['external_id'] ?? '',
            'status' => match ($status) {
                'PAID' => 'paid',
                'EXPIRED' => 'expired',
                'FAILED' => 'failed',
                default => 'pending',
            },
            'raw' => $payload,
        ];
    }

    public function name(): string
    {
        return 'xendit';
    }
}
