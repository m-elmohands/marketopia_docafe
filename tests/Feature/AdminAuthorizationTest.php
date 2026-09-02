<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Support\Authorization\PermissionRegistry;
use Database\Seeders\AuthorizationSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuthorizationSeeder::class);
    }

    public function test_user_without_panel_permission_cannot_access_filament(): void
    {
        $user = User::factory()->create();
        $panel = Filament::getPanel('admin');

        $this->assertFalse($user->canAccessPanel($panel));

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_panel_and_resource_permissions_are_independent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            PermissionRegistry::PANEL_ACCESS,
            PermissionRegistry::for('products', 'view_any'),
        ]);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($user->can('viewAny', Product::class));
        $this->assertFalse($user->can('create', Product::class));
    }

    public function test_admin_role_receives_all_registered_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can(PermissionRegistry::PANEL_ACCESS));
        $this->assertTrue($admin->can('viewAny', Product::class));
        $this->assertTrue($admin->can('create', Product::class));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }
}
