<?php

namespace App\Policies;

class PaymentIntentionPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'payment_intentions';
    }
}
