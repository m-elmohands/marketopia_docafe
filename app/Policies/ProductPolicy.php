<?php

namespace App\Policies;

class ProductPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'products';
    }
}
