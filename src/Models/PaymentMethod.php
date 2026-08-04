<?php

declare(strict_types=1);

namespace Moe\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'gateway',
        'type',
        'label',
        'details',
        'is_default',
    ];

    protected $casts = [
        'details' => 'array',
        'is_default' => 'boolean',
    ];

    protected $table = 'payment_methods';
}
