<?php

namespace App\Policies;

class ArticlePolicy extends ResourcePolicy
{
    protected function permissionResource(): string
    {
        return 'articles';
    }
}
