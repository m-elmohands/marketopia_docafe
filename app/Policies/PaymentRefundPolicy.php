<?php

namespace App\Policies;

class PaymentRefundPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'payment_refunds';
    }
}
