<?php

namespace Tests\Feature;

use App\Enums\SettingType;
use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_the_fixed_application_settings_form(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(ManageSettings::class)
            ->fillForm([
                'identity' => [
                    'en' => ['name' => 'Do Café', 'description' => 'Specialty coffee in Cairo.'],
                    'ar' => ['name' => 'دو كافيه', 'description' => 'قهوة مختصة في القاهرة.'],
                ],
                'contact' => [
                    'email' => 'hello@example.com',
                    'phone' => '+201000000000',
                    'en_address' => 'Cairo, Egypt',
                    'ar_address' => 'القاهرة، مصر',
                ],
                'commerce' => ['delivery_fee' => 2_500, 'tax_percentage' => 14],
                'social' => [
                    'facebook_url' => 'https://facebook.com/do-cafe',
                    'instagram_url' => 'https://instagram.com/do-cafe',
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified(__('Settings saved successfully.'));

        $this->assertSame(9, Setting::count());

        $name = Setting::where('key', 'cafe_name')->firstOrFail();
        $deliveryFee = Setting::where('key', 'delivery_fee')->firstOrFail();

        $this->assertSame('Do Café', $name->translate('en')->value);
        $this->assertSame('دو كافيه', $name->translate('ar')->value);
        $this->assertSame(SettingType::Integer, $deliveryFee->type);
        $this->assertSame('2500', $deliveryFee->translate('en')->value);
    }

    public function test_settings_resource_has_only_the_single_form_page(): void
    {
        $this->assertTrue(SettingResource::hasPage('index'));
        $this->assertFalse(SettingResource::hasPage('create'));
        $this->assertFalse(SettingResource::hasPage('edit'));
    }
}
