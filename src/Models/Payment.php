<?php

declare(strict_types=1);

namespace MOE\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'gateway',
        'transaction_id',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'method',
        'channel',
        'paid_at',
        'expired_at',
        'metadata',
        'raw_response',
        'failure_reason',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'metadata' => 'array',
        'raw_response' => 'array',
    ];

    protected $table = 'payments';

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (! $payment->getKey()) {
                $payment->{$payment->getKeyName()} = (string) str()->uuid();
            }
        });
    }
}
