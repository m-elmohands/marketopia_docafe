<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
        config()->set('admin.seed', [
            'name' => 'Store Admin',
            'email' => 'admin@example.com',
            'password' => 'a-secure-password',
        ]);
    }

    public function test_root_seeder_persists_all_bilingual_translations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame(4, Category::count());
        $this->assertSame(8, DB::table('category_translations')->count());
        $this->assertSame(12, Product::count());
        $this->assertSame(24, DB::table('product_translations')->count());
        $this->assertSame(48, ProductStage::count());
        $this->assertSame(96, DB::table('product_stage_translations')->count());
        $this->assertSame(4, Article::count());
        $this->assertSame(8, DB::table('article_translations')->count());
    }
}
