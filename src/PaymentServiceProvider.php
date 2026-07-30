<?php

declare(strict_types=1);

namespace MOE\Payment;

use Illuminate\Support\ServiceProvider;
use MOE\Payment\Contracts\PaymentGatewayInterface;
use MOE\Payment\Services\GatewayManager;
use MOE\Payment\Services\PaymentService;

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
