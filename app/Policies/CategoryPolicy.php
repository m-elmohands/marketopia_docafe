<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class CategoryPolicy extends ResourcePolicy
{
    public function delete(User $user, Model $model): bool|Response
    {
        $authorized = parent::delete($user, $model);

        if ($authorized !== true || ! $model instanceof Category) {
            return $authorized;
        }

        return $model->canBeDeleted()
            ? true
            : Response::deny(__('A category containing products cannot be deleted.'));
    }

    public function forceDelete(User $user, Model $model): bool|Response
    {
        $authorized = parent::forceDelete($user, $model);

        if ($authorized !== true || ! $model instanceof Category) {
            return $authorized;
        }

        return $model->canBeDeleted()
            ? true
            : Response::deny(__('A category containing products cannot be deleted.'));
    }

    protected function permissionResource(): string
    {
        return 'categories';
    }
}
