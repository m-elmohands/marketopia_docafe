<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy extends ResourcePolicy
{
    public function viewStorefront(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    protected function permissionResource(): string
    {
        return 'orders';
    }
}
