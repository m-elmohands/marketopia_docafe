<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Database\Seeders\CoffeeArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoffeeArticleSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('media-library.disk_name'));
    }

    public function test_it_seeds_bilingual_published_coffee_articles_with_images(): void
    {
        $this->seed(AuthorizationSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->seed(CoffeeArticleSeeder::class);

        $this->assertSame(4, Article::count());
        $this->assertSame(8, DB::table('article_translations')->count());

        $article = Article::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', 'how-espresso-is-made'))
            ->firstOrFail();

        $this->assertSame('How Espresso Is Made', $article->translate('en')->title);
        $this->assertSame('كيف يُحضّر الإسبريسو', $article->translate('ar')->title);
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertSame($admin->id, $article->author_id);
        $this->assertTrue($article->is_featured);
        $this->assertNotNull($article->published_at);
        $this->assertCount(1, $article->getMedia('article-images'));
    }

    public function test_it_can_run_repeatedly_without_duplicate_articles_or_media(): void
    {
        $this->seed(CoffeeArticleSeeder::class);

        $article = Article::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', 'how-espresso-is-made'))
            ->firstOrFail();
        $article->translateOrNew('en')->excerpt = 'Manually customized excerpt.';
        $article->save();

        $this->seed(CoffeeArticleSeeder::class);

        $this->assertSame(4, Article::count());
        $this->assertSame(8, DB::table('article_translations')->count());
        $this->assertSame(4, DB::table('media')->where('collection_name', 'article-images')->count());
        $this->assertSame(
            'Learn how grind size, dose, pressure, and time create a balanced espresso shot.',
            $article->fresh()->translate('en')->excerpt,
        );
    }
}
