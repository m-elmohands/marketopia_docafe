<?php

namespace App\Policies;

class ProductStagePolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'products';
    }
}
