<?php

namespace Tests\Feature;

use App\Enums\CatalogStatus;
use App\Enums\Currency;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductStagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
    }

    public function test_admin_can_add_ordered_bilingual_stages_from_the_product_form(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(ListProducts::class)
            ->callAction('create', data: [
                'sku' => 'COF-STAGES-001',
                'status' => CatalogStatus::Active->value,
                'price' => 9_500,
                'currency' => Currency::EGP->value,
                'en' => ['name' => 'Pour Over', 'short_description' => null, 'description' => null],
                'ar' => ['name' => 'قهوة مقطرة', 'short_description' => null, 'description' => null],
                'stages' => [
                    [
                        'en' => ['title' => 'Grinding', 'description' => 'Grind the beans fresh.'],
                        'ar' => ['title' => 'الطحن', 'description' => 'اطحن حبوب القهوة طازجة.'],
                    ],
                    [
                        'en' => ['title' => 'Brewing', 'description' => 'Pour water in slow circles.'],
                        'ar' => ['title' => 'التحضير', 'description' => 'اسكب الماء ببطء بشكل دائري.'],
                    ],
                ],
            ])
            ->assertHasNoFormErrors();

        $product = Product::where('sku', 'COF-STAGES-001')->firstOrFail();
        $stages = $product->stages()->with('translations')->get();

        $this->assertCount(2, $stages);
        $this->assertSame([1, 2], $stages->pluck('sort_order')->all());
        $this->assertSame('Grinding', $stages[0]->translate('en')->title);
        $this->assertSame('الطحن', $stages[0]->translate('ar')->title);
        $this->assertSame('Brewing', $stages[1]->translate('en')->title);
        $this->assertSame('التحضير', $stages[1]->translate('ar')->title);
    }
}
