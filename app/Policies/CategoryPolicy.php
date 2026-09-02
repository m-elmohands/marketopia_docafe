<?php

namespace App\Policies;

class CategoryPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'categories';
    }
}
