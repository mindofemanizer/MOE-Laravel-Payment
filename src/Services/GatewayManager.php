<?php

declare(strict_types=1);

namespace MOE\Payment\Services;

use InvalidArgumentException;
use MOE\Payment\Contracts\PaymentGatewayInterface;
use MOE\Payment\Gateways\MidtransGateway;
use MOE\Payment\Gateways\StripeGateway;
use MOE\Payment\Gateways\XenditGateway;

class GatewayManager
{
    protected array $gateways = [];

    protected array $drivers = [
        'midtrans' => MidtransGateway::class,
        'xendit' => XenditGateway::class,
        'stripe' => StripeGateway::class,
    ];

    public function register(string $name, string|PaymentGatewayInterface $gateway, array $config = []): void
    {
        if (is_string($gateway)) {
            $gateway = new $gateway($config);
        }

        $this->gateways[$name] = $gateway;
    }

    public function get(string $name, array $config = []): PaymentGatewayInterface
    {
        if (isset($this->gateways[$name])) {
            return $this->gateways[$name];
        }

        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not supported.");
        }

        $class = $this->drivers[$name];

        return $this->gateways[$name] = new $class($config);
    }

    public function gateways(): array
    {
        return array_keys($this->drivers);
    }
}
