<?php

declare(strict_types=1);

use Moe\Payment\Facades\Payment;
use Moe\Payment\Models\Payment as PaymentModel;
use Moe\Payment\Services\PaymentService;
use Moe\Payment\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = app(PaymentService::class);
    $this->payment = PaymentModel::create([
        'gateway' => 'midtrans',
        'invoice_number' => 'INV-001',
        'amount' => 50000,
        'currency' => 'IDR',
    ]);
});

it('resolves payment service from container', function () {
    expect($this->service)->toBeInstanceOf(PaymentService::class);
});

it('facade delegates to service', function () {
    $gateway = Payment::gateway('midtrans');

    expect($gateway->name())->toBe('midtrans');
});

it('charge returns array response', function () {
    $result = $this->service->charge($this->payment, 'midtrans');

    expect($result)->toBeArray();
});

it('refund returns array response', function () {
    $this->payment->update(['transaction_id' => 'test-trx-123']);

    $result = $this->service->refund($this->payment, null, 'midtrans');

    expect($result)->toBeArray();
});

it('status returns array', function () {
    $result = $this->service->status('test-trx-123', 'midtrans');

    expect($result)->toBeArray();
});

it('gateway returns specific gateway', function () {
    $gateway = $this->service->gateway('stripe');

    expect($gateway->name())->toBe('stripe');
});

it('gateway returns default gateway', function () {
    $gateway = $this->service->gateway();

    expect($gateway->name())->toBe('midtrans');
});

it('handleWebhook processes payload', function () {
    $payload = [
        'transaction_id' => 'trx-001',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
    ];

    $result = $this->service->handleWebhook('midtrans', $payload);

    expect($result)->toBeArray();
    expect($result['status'])->toBe('paid');
});

it('creates payment with uuid', function () {
    expect($this->payment->getKey())->toBeString();
    expect(strlen($this->payment->getKey()))->toBe(36);
});

it('payment has fillable attributes', function () {
    expect($this->payment->gateway)->toBe('midtrans');
    expect((float) $this->payment->amount)->toBe(50000.0);
    expect($this->payment->status)->toBe('pending');
});
