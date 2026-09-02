<?php

namespace Tests\Feature;

use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TranslationTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_tabs_keep_their_own_language_when_panel_locale_is_arabic(): void
    {
        $originalLocale = app()->getLocale();

        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);
        app()->setLocale('ar');

        Livewire::test(ManageSettings::class)
            ->assertSee('English')
            ->assertSee('العربية')
            ->assertSee('Café name')
            ->assertSee('اسم المقهى')
            ->assertSee('dir="ltr"', false)
            ->assertSee('dir="rtl"', false);

        app()->setLocale($originalLocale);
    }
}
