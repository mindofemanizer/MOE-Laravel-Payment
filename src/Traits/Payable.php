<?php

declare(strict_types=1);

namespace MOE\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use MOE\Payment\Models\Payment;
use MOE\Payment\Models\PaymentMethod;

trait Payable
{
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function paymentMethods(): MorphMany
    {
        return $this->morphMany(PaymentMethod::class, 'payable');
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function createPayment(array $data): Payment
    {
        return $this->payments()->create($data);
    }
}
