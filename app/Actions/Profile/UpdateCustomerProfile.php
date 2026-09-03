<?php

namespace App\Actions\Profile;

use App\Models\User;

class UpdateCustomerProfile
{
    public function handle(User $user, array $data): User
    {
        unset($data['current_password'], $data['password_confirmation']);
        $user->update($data);

        return $user->refresh();
    }
}
