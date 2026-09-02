<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CoffeeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoffeeCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
    }

    public function test_it_seeds_a_bilingual_prepared_coffee_drinks_catalog(): void
    {
        $this->seed(CoffeeCatalogSeeder::class);

        $this->assertSame(4, Category::count());
        $this->assertSame(12, Product::count());

        $spanishLatte = Product::where('sku', 'COF-SIG-002')->firstOrFail();

        $this->assertSame('Spanish Latte', $spanishLatte->translate('en')->name);
        $this->assertSame('سبانيش لاتيه', $spanishLatte->translate('ar')->name);
        $this->assertSame(11_500, $spanishLatte->price);
        $this->assertSame('EGP', $spanishLatte->currency);
        $this->assertSame('active', $spanishLatte->status);
        $this->assertFalse($spanishLatte->track_inventory);
        $this->assertTrue($spanishLatte->is_featured);
        $this->assertNotNull($spanishLatte->published_at);
        $this->assertSame('prepared_coffee_drink', $spanishLatte->metadata['product_type']);
        $this->assertCount(1, $spanishLatte->getMedia('product-images'));
        $this->assertTrue($spanishLatte->categories->contains(
            fn (Category $category): bool => $category->translate('en')->name === 'Signature Coffee',
        ));
    }

    public function test_it_can_be_run_repeatedly_without_duplicates(): void
    {
        $this->seed(CoffeeCatalogSeeder::class);
        $this->seed(CoffeeCatalogSeeder::class);

        $this->assertSame(4, Category::count());
        $this->assertSame(12, Product::count());
        $this->assertSame(8, DB::table('category_translations')->count());
        $this->assertSame(24, DB::table('product_translations')->count());
        $this->assertSame(16, DB::table('media')->count());
    }
}
