<?php

namespace App\Policies;

class UserPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'users';
    }
}
