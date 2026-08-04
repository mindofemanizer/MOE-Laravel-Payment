<?php

declare(strict_types=1);

namespace Moe\Payment;

use Illuminate\Support\ServiceProvider;
use Moe\Payment\Contracts\PaymentGatewayInterface;
use Moe\Payment\Services\GatewayManager;
use Moe\Payment\Services\PaymentService;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/moe-payment.php', 'moe-payment');

        $this->app->singleton(GatewayManager::class, fn () => new GatewayManager);
        $this->app->singleton(PaymentService::class, fn ($app) => new PaymentService(
            $app->make(GatewayManager::class),
            $app->make('config')->get('moe-payment'),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/moe-payment.php' => config_path('moe-payment.php'),
            ], 'moe-payment-config');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
