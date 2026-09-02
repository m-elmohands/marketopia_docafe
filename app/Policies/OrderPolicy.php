<?php

namespace App\Policies;

class OrderPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'orders';
    }
}
