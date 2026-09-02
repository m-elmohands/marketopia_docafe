<?php

namespace App\Policies;

class PaymentTransactionPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'payment_transactions';
    }
}
