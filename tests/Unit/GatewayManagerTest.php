<?php

declare(strict_types=1);

use Moe\Payment\Contracts\PaymentGatewayInterface;
use Moe\Payment\Gateways\MidtransGateway;
use Moe\Payment\Services\GatewayManager;

beforeEach(function () {
    $this->manager = new GatewayManager;
});

it('resolves midtrans gateway', function () {
    $gateway = $this->manager->get('midtrans');

    expect($gateway)->toBeInstanceOf(PaymentGatewayInterface::class);
    expect($gateway->name())->toBe('midtrans');
});

it('resolves xendit gateway', function () {
    $gateway = $this->manager->get('xendit');

    expect($gateway)->toBeInstanceOf(PaymentGatewayInterface::class);
    expect($gateway->name())->toBe('xendit');
});

it('resolves stripe gateway', function () {
    $gateway = $this->manager->get('stripe');

    expect($gateway)->toBeInstanceOf(PaymentGatewayInterface::class);
    expect($gateway->name())->toBe('stripe');
});

it('registers custom gateway instance', function () {
    $custom = new MidtransGateway;
    $this->manager->register('custom-midtrans', $custom);

    expect($this->manager->get('custom-midtrans'))->toBe($custom);
});

it('registers custom gateway class', function () {
    $this->manager->register('dynamic-gateway', MidtransGateway::class);

    expect($this->manager->get('dynamic-gateway'))->toBeInstanceOf(MidtransGateway::class);
});

it('throws for unknown gateway', function () {
    $this->manager->get('unknown-gateway');
})->throws(InvalidArgumentException::class);

it('lists available gateways', function () {
    $gateways = $this->manager->gateways();

    expect($gateways)->toContain('midtrans', 'xendit', 'stripe');
});
