<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Authorization\PermissionRegistry;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesResourcePermissions
{
    abstract protected function permissionResource(): string;

    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'view_any');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allowed($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allowed($user, 'update');
    }

    public function delete(User $user, Model $model): bool|Response
    {
        return $this->allowed($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allowed($user, 'restore');
    }

    public function restoreAny(User $user): bool
    {
        return $this->allowed($user, 'restore');
    }

    public function forceDelete(User $user, Model $model): bool|Response
    {
        return $this->allowed($user, 'force_delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allowed($user, 'force_delete');
    }

    public function replicate(User $user, Model $model): bool
    {
        return $this->allowed($user, 'replicate');
    }

    public function reorder(User $user): bool
    {
        return $this->allowed($user, 'reorder');
    }

    private function allowed(User $user, string $action): bool
    {
        return $user->can(PermissionRegistry::for($this->permissionResource(), $action));
    }
}
