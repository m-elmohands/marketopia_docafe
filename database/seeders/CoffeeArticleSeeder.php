<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CoffeeArticleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $author = Role::query()->where('name', 'admin')->where('guard_name', 'web')->exists()
                ? User::role('admin')->oldest()->first()
                : null;

            foreach ($this->articles() as $data) {
                $article = Article::query()
                    ->whereHas('translations', fn (Builder $query): Builder => $query
                        ->where('locale', 'en')
                        ->where('slug', Str::slug($data['en']['title'])))
                    ->first() ?? new Article;

                $article->fill([
                    'author_id' => $author?->id,
                    'status' => ArticleStatus::Published,
                    'is_featured' => $data['is_featured'],
                ]);
                $article->published_at ??= now()->subDays($data['published_days_ago']);

                foreach (['en', 'ar'] as $locale) {
                    $article->translateOrNew($locale)->fill($data[$locale]);
                }

                $article->save();
                $this->attachImage($article, $data['image']);
            }
        });
    }

    /** @return list<array<string, mixed>> */
    private function articles(): array
    {
        return [
            $this->article(
                'How Espresso Is Made',
                'كيف يُحضّر الإسبريسو',
                'Learn how grind size, dose, pressure, and time create a balanced espresso shot.',
                'تعرّف على دور درجة الطحن والجرعة والضغط والوقت في تحضير جرعة إسبريسو متوازنة.',
                '<p>Espresso begins with freshly roasted beans ground immediately before brewing. A measured dose is distributed evenly and tamped to create consistent resistance.</p><p>Hot water then passes through the coffee under pressure. A balanced extraction produces sweetness, pleasant acidity, body, and a smooth crema.</p>',
                '<p>يبدأ الإسبريسو بحبوب محمصة طازجة تُطحن مباشرة قبل التحضير. توزّع الجرعة بالتساوي وتُكبس للحصول على مقاومة متجانسة.</p><p>يمر الماء الساخن بعد ذلك عبر القهوة تحت الضغط. وينتج عن الاستخلاص المتوازن حلاوة وحموضة لطيفة وقوام غني وطبقة كريمية ناعمة.</p>',
                'espresso.png',
                true,
                3,
            ),
            $this->article(
                'Cappuccino, Latte, and Flat White Explained',
                'الفرق بين الكابتشينو واللاتيه والفلات وايت',
                'A simple guide to three popular milk-based coffee drinks.',
                'دليل مبسط لثلاثة من أشهر مشروبات القهوة بالحليب.',
                '<p>These drinks share espresso and steamed milk, but their proportions and milk texture differ. Cappuccino has an airy foam cap, latte contains more steamed milk, and flat white uses fine microfoam with a stronger coffee character.</p><p>Choose cappuccino for a foamy texture, latte for a gentle creamy cup, or flat white for a velvety drink with a bolder espresso taste.</p>',
                '<p>تشترك هذه المشروبات في الإسبريسو والحليب المبخر، لكنها تختلف في النسب وقوام الحليب. يتميز الكابتشينو برغوة كثيفة، ويحتوي اللاتيه على حليب أكثر، بينما يستخدم الفلات وايت رغوة دقيقة مع نكهة قهوة أقوى.</p><p>اختر الكابتشينو للقوام الرغوي، أو اللاتيه لمذاق كريمي هادئ، أو الفلات وايت لمشروب مخملي بنكهة إسبريسو أوضح.</p>',
                'hot-coffee.png',
                true,
                7,
            ),
            $this->article(
                'Cold Brew vs. Iced Coffee',
                'الفرق بين الكولد برو والقهوة المثلجة',
                'Discover how brewing method changes the flavor of cold coffee.',
                'اكتشف كيف تؤثر طريقة التحضير في نكهة القهوة الباردة.',
                '<p>Iced coffee is brewed hot and cooled over ice, preserving bright aromas and familiar brewed-coffee character. Cold brew is steeped in cool water for many hours, creating a smoother and naturally sweeter profile.</p><p>Both are refreshing: choose iced coffee for clarity and brightness, or cold brew for a mellow, low-acidity cup.</p>',
                '<p>تُحضّر القهوة المثلجة ساخنة ثم تُبرّد فوق الثلج، مما يحافظ على الروائح الواضحة وطابع القهوة المعتاد. أما الكولد برو فيُنقع في ماء بارد لساعات طويلة، فينتج مذاقًا أنعم وأكثر حلاوة بصورة طبيعية.</p><p>كلاهما منعش؛ اختر القهوة المثلجة للنكهة الواضحة، أو الكولد برو لمذاق هادئ وأقل حموضة.</p>',
                'iced-coffee.png',
                false,
                12,
            ),
            $this->article(
                'Why Spanish Latte Is So Popular',
                'لماذا يحظى السبانيش لاتيه بهذه الشعبية؟',
                'The creamy, lightly sweet coffee drink loved hot and iced.',
                'مشروب قهوة كريمي بحلاوة خفيفة يُقدم ساخنًا أو مثلجًا.',
                '<p>Spanish latte balances bold espresso with creamy milk and restrained sweetness. Its approachable flavor makes it an excellent introduction to espresso while still preserving the coffee character.</p><p>Careful proportions are essential: sweetness should support the espresso rather than cover it.</p>',
                '<p>يجمع السبانيش لاتيه بين الإسبريسو القوي والحليب الكريمي وحلاوة معتدلة. ويجعله مذاقه السهل مدخلًا رائعًا لمشروبات الإسبريسو مع الحفاظ على طابع القهوة.</p><p>النسب الدقيقة مهمة؛ إذ يجب أن تدعم الحلاوة نكهة الإسبريسو بدلًا من إخفائها.</p>',
                'signature-coffee.png',
                true,
                18,
            ),
        ];
    }

    /** @return array<string, mixed> */
    private function article(string $enTitle, string $arTitle, string $enExcerpt, string $arExcerpt, string $enContent, string $arContent, string $image, bool $isFeatured, int $publishedDaysAgo): array
    {
        return [
            'image' => $image,
            'is_featured' => $isFeatured,
            'published_days_ago' => $publishedDaysAgo,
            'en' => ['title' => $enTitle, 'excerpt' => $enExcerpt, 'content' => $enContent, 'meta_title' => $enTitle, 'meta_description' => $enExcerpt],
            'ar' => ['title' => $arTitle, 'excerpt' => $arExcerpt, 'content' => $arContent, 'meta_title' => $arTitle, 'meta_description' => $arExcerpt],
        ];
    }

    private function attachImage(Article $article, string $filename): void
    {
        if ($article->hasMedia('article-images')) {
            return;
        }

        $article
            ->addMedia(database_path("seeders/assets/coffee/{$filename}"))
            ->preservingOriginal()
            ->toMediaCollection('article-images');
    }
}
