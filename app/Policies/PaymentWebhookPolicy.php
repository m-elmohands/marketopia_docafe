<?php

namespace App\Policies;

class PaymentWebhookPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'payment_webhooks';
    }
}
