<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResourcePermissions;

abstract class ResourcePolicy
{
    use AuthorizesResourcePermissions;
}
