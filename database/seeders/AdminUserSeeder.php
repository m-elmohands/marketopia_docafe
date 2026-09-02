<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('admin.seed.name');
        $email = (string) config('admin.seed.email');
        $password = (string) config('admin.seed.password');

        $this->validateCredentials($name, $email, $password);

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => $name,
            'password' => $password,
            'email_verified_at' => now(),
        ])->save();

        $admin->syncRoles(['admin']);
    }

    private function validateCredentials(string $name, string $email, string $password): void
    {
        if ($name === '' || $email === '' || $password === '') {
            throw new LogicException(
                'ADMIN_USER_NAME, ADMIN_USER_EMAIL, and ADMIN_USER_PASSWORD must be configured before seeding.',
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new LogicException('ADMIN_USER_EMAIL must be a valid email address.');
        }

        if (mb_strlen($password) < 12) {
            throw new LogicException('ADMIN_USER_PASSWORD must contain at least 12 characters.');
        }
    }
}
