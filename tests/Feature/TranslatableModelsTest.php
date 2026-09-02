<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslatableModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_translations_are_stored_and_resolved_by_locale(): void
    {
        $product = Product::create([
            'sku' => 'COFFEE-001',
            'price' => 2500,
            'en' => [
                'name' => 'Coffee',
                'slug' => 'coffee',
                'description' => 'Fresh coffee.',
            ],
            'ar' => [
                'name' => 'قهوة',
                'slug' => 'قهوة',
                'description' => 'قهوة طازجة.',
            ],
        ]);

        $this->assertSame('Coffee', $product->translate('en')->name);
        $this->assertSame('قهوة', $product->translate('ar')->name);
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'locale' => 'ar',
            'slug' => 'قهوة',
        ]);
    }

    public function test_articles_and_settings_store_translated_content(): void
    {
        $author = User::factory()->create();

        $article = Article::create([
            'author_id' => $author->id,
            'en' => [
                'title' => 'Brewing Guide',
                'slug' => 'brewing-guide',
                'content' => 'English guide.',
            ],
            'ar' => [
                'title' => 'دليل التحضير',
                'slug' => 'دليل-التحضير',
                'content' => 'الدليل العربي.',
            ],
        ]);

        $setting = Setting::create([
            'key' => 'store.tagline',
            'en' => ['value' => 'Made for coffee lovers'],
            'ar' => ['value' => 'صنع لعشاق القهوة'],
        ]);

        $this->assertSame('دليل التحضير', $article->translate('ar')->title);
        $this->assertSame('صنع لعشاق القهوة', $setting->translate('ar')->value);
    }
}
