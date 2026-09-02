<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_with_a_hashed_password(): void
    {
        config()->set('admin.seed', [
            'name' => 'Store Admin',
            'email' => 'admin@example.com',
            'password' => 'a-secure-password',
        ]);

        $this->seed(AuthorizationSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Store Admin', $admin->name);
        $this->assertTrue(Hash::check('a-secure-password', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('admin.access'));
    }

    public function test_it_updates_the_existing_admin_when_run_again(): void
    {
        $this->seed(AuthorizationSeeder::class);

        config()->set('admin.seed', [
            'name' => 'Store Admin',
            'email' => 'admin@example.com',
            'password' => 'initial-password',
        ]);
        $this->seed(AdminUserSeeder::class);

        config()->set('admin.seed', [
            'name' => 'Updated Admin',
            'email' => 'admin@example.com',
            'password' => 'updated-password',
        ]);
        $this->seed(AdminUserSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@example.com')->count());

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Updated Admin', $admin->name);
        $this->assertTrue(Hash::check('updated-password', $admin->password));
    }

    public function test_it_rejects_missing_credentials(): void
    {
        config()->set('admin.seed', [
            'name' => null,
            'email' => null,
            'password' => null,
        ]);

        $this->expectException(LogicException::class);

        $this->seed(AdminUserSeeder::class);
    }
}
