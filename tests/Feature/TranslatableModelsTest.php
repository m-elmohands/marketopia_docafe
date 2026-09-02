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
                'name' => 'Fresh Coffee',
                'slug' => 'user-controlled-slug',
                'description' => 'Fresh coffee.',
            ],
            'ar' => [
                'name' => 'قهوة طازجة',
                'slug' => 'another-user-controlled-slug',
                'description' => 'قهوة طازجة.',
            ],
        ]);

        $this->assertSame('Fresh Coffee', $product->translate('en')->name);
        $this->assertSame('fresh-coffee', $product->translate('en')->slug);
        $this->assertSame('قهوة طازجة', $product->translate('ar')->name);
        $this->assertSame('قهوة-طازجة', $product->translate('ar')->slug);
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'locale' => 'ar',
            'slug' => 'قهوة-طازجة',
        ]);
    }

    public function test_articles_and_settings_store_translated_content(): void
    {
        $author = User::factory()->create();

        $article = Article::create([
            'author_id' => $author->id,
            'en' => [
                'title' => 'Brewing Guide',
                'content' => 'English guide.',
            ],
            'ar' => [
                'title' => 'دليل التحضير',
                'content' => 'الدليل العربي.',
            ],
        ]);

        $setting = Setting::create([
            'key' => 'store.tagline',
            'en' => ['value' => 'Made for coffee lovers'],
            'ar' => ['value' => 'صنع لعشاق القهوة'],
        ]);

        $this->assertSame('دليل التحضير', $article->translate('ar')->title);
        $this->assertSame('دليل-التحضير', $article->translate('ar')->slug);
        $this->assertSame('صنع لعشاق القهوة', $setting->translate('ar')->value);
    }

    public function test_duplicate_translated_names_receive_unique_locale_scoped_slugs(): void
    {
        Product::create([
            'sku' => 'COFFEE-001',
            'price' => 2500,
            'en' => ['name' => 'Coffee Beans'],
        ]);

        $secondProduct = Product::create([
            'sku' => 'COFFEE-002',
            'price' => 3000,
            'en' => ['name' => 'Coffee Beans'],
        ]);

        $this->assertSame('coffee-beans-2', $secondProduct->translate('en')->slug);
    }
}
