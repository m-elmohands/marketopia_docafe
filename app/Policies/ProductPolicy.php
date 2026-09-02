<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class ProductPolicy extends ResourcePolicy
{
    public function delete(User $user, Model $model): bool|Response
    {
        $authorized = parent::delete($user, $model);

        if ($authorized !== true || ! $model instanceof Product) {
            return $authorized;
        }

        return $model->canBeDeleted()
            ? true
            : Response::deny(__('A product included in an order cannot be deleted.'));
    }

    public function forceDelete(User $user, Model $model): bool|Response
    {
        $authorized = parent::forceDelete($user, $model);

        if ($authorized !== true || ! $model instanceof Product) {
            return $authorized;
        }

        return $model->canBeDeleted()
            ? true
            : Response::deny(__('A product included in an order cannot be deleted.'));
    }

    protected function permissionResource(): string
    {
        return 'products';
    }
}
