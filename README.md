# MOE Laravel Payment

Package pembayaran multigateway untuk Laravel â€” Midtrans, Xendit, Stripe.

## Persyaratan

- PHP `^8.2`
- Laravel `^11 | ^12 | ^13`

## Instalasi

```bash
composer require moe/laravel-payment:dev-main
php artisan vendor:publish --provider="Moe\\Payment\\PaymentServiceProvider" --tag="moe-payment-config"
php artisan migrate
```

## Mulai Cepat

### 1. Charge pembayaran

```php
use Moe\Payment\Facades\Payment;
use Moe\Payment\Models\Payment as PaymentModel;

$payment = PaymentModel::create([
    'invoice_number' => 'INV-001',
    'amount' => 50000,
    'currency' => 'IDR',
]);

$result = Payment::charge($payment, 'midtrans', [
    'customer' => ['name' => 'Budi', 'email' => 'budi@example.com'],
]);
```

### 2. Refund

```php
$result = Payment::refund($payment, null, 'midtrans');
```

### 3. Cek status

```php
$status = Payment::status('trx-id-123', 'midtrans');
```

### 4. Webhook

```php
$result = Payment::handleWebhook('midtrans', $request->all());
// ['status' => 'paid|failed|expired|refunded', 'transaction_id' => '...', 'order_id' => '...']
```

## Gateway Didukung

| Gateway    | Driver        | Webhook otomatis mapping status |
|------------|---------------|---------------------------------|
| `midtrans` | MidtransGateway | settlement/pending/deny/expire  |
| `xendit`   | XenditGateway   | PAID/EXPIRED/FAILED             |
| `stripe`   | StripeGateway   | charge.succeeded/failed/refunded |

### Gateway Kustom

```php
use Moe\Payment\Contracts\PaymentGatewayInterface;

class MyGateway implements PaymentGatewayInterface
{
    public function charge(Payment $payment, array $options = []): array { ... }
    public function refund(Payment $payment, ?float $amount = null): array { ... }
    public function status(string $transactionId): array { ... }
    public function webhook(array $payload): array { ... }
    public function name(): string { return 'mygateway'; }
}

// Daftarkan
$manager = app(\Moe\Payment\Services\GatewayManager::class);
$manager->register('mygateway', MyGateway::class);
```

## Payable (Trait)

```php
use Moe\Payment\Traits\Payable;

class Order extends Model
{
    use Payable;

    // $order->payments()         -> MorphMany
    // $order->paymentMethods()   -> MorphMany
    // $order->latestPayment()    -> ?Payment
    // $order->createPayment([])  -> Payment
}
```

## Konfigurasi

```php
// config/moe-payment.php
return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'midtrans'),
    'currency' => env('PAYMENT_CURRENCY', 'IDR'),

    'gateways' => [
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],
        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'is_production' => env('XENDIT_IS_PRODUCTION', false),
        ],
        'stripe' => [
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
    ],
];
```

## Testing

```bash
composer test
```

## Lisensi

MIT Â© MOE (MindOfEmanizer)
