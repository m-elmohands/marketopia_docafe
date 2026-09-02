<?php

namespace App\Policies;

class SettingPolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'settings';
    }
}
