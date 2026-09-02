<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoffeeCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = $this->seedCategories();

            foreach ($this->products() as $sku => $data) {
                $categoryKeys = $data['categories'];
                $image = $this->imageFor($categoryKeys);
                unset($data['categories']);

                $product = Product::firstOrNew(['sku' => $sku]);
                $product->fill([
                    'status' => 'active',
                    'price' => $data['price'],
                    'currency' => 'EGP',
                    'track_inventory' => false,
                    'stock_quantity' => 0,
                    'is_featured' => $data['is_featured'],
                    'metadata' => ['product_type' => 'prepared_coffee_drink'],
                ]);
                $product->published_at ??= now();

                foreach (['en', 'ar'] as $locale) {
                    $product->translateOrNew($locale)->fill($data[$locale]);
                }

                $product->save();
                $product->categories()->sync(
                    collect($categoryKeys)->map(fn (string $key): int => $categories[$key]->id),
                );
                $this->attachImage($product, $image, 'product-images');
            }
        });
    }

    /** @return array<string, Category> */
    private function seedCategories(): array
    {
        $seeded = [];

        foreach ($this->categories() as $key => $data) {
            $category = Category::query()
                ->whereHas('translations', fn (Builder $query): Builder => $query
                    ->where('locale', 'en')
                    ->where('slug', $data['slug']))
                ->first() ?? new Category;

            $category->fill([
                'status' => 'active',
                'sort_order' => $data['sort_order'],
                'is_featured' => true,
            ]);

            foreach (['en', 'ar'] as $locale) {
                $category->translateOrNew($locale)->fill($data[$locale]);
            }

            $category->save();
            $this->attachImage($category, $this->imageFor([$key]), 'category-images');
            $seeded[$key] = $category;
        }

        return $seeded;
    }

    /** @return array<string, array<string, mixed>> */
    private function categories(): array
    {
        return [
            'espresso' => $this->category('espresso-bar', 10, 'Espresso Bar', 'ركن الإسبريسو', 'Classic espresso-based coffee drinks.', 'مشروبات القهوة الكلاسيكية المحضرة بالإسبريسو.'),
            'hot' => $this->category('hot-coffee', 20, 'Hot Coffee', 'القهوة الساخنة', 'Freshly prepared hot coffee drinks.', 'مشروبات قهوة ساخنة محضرة طازجة.'),
            'iced' => $this->category('iced-coffee', 30, 'Iced Coffee', 'القهوة المثلجة', 'Chilled coffee drinks served over ice.', 'مشروبات قهوة باردة تقدم مع الثلج.'),
            'signature' => $this->category('signature-coffee', 40, 'Signature Coffee', 'القهوة المميزة', 'Distinctive recipes created by our baristas.', 'وصفات قهوة مميزة من إعداد خبراء القهوة لدينا.'),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function products(): array
    {
        return [
            'COF-ESP-001' => $this->drink('Espresso', 'إسبريسو', 5500, ['espresso'], 'A rich single shot with a smooth crema.', 'جرعة إسبريسو غنية بطبقة كريمية ناعمة.'),
            'COF-ESP-002' => $this->drink('Double Espresso', 'دبل إسبريسو', 7000, ['espresso'], 'Two bold espresso shots for a stronger coffee.', 'جرعتان من الإسبريسو لمذاق قهوة أقوى.'),
            'COF-HOT-001' => $this->drink('Americano', 'أمريكانو', 6500, ['espresso', 'hot'], 'Espresso softened with hot water.', 'إسبريسو مخفف بالماء الساخن.'),
            'COF-HOT-002' => $this->drink('Cappuccino', 'كابتشينو', 8500, ['hot'], 'Espresso with steamed milk and a generous foam cap.', 'إسبريسو مع حليب مبخر وطبقة غنية من الرغوة.', true),
            'COF-HOT-003' => $this->drink('Café Latte', 'كافيه لاتيه', 9000, ['hot'], 'Smooth espresso balanced with creamy steamed milk.', 'إسبريسو ناعم ومتوازن مع حليب مبخر كريمي.', true),
            'COF-HOT-004' => $this->drink('Flat White', 'فلات وايت', 9500, ['hot'], 'Double espresso with velvety microfoam milk.', 'دبل إسبريسو مع حليب مخملي ناعم.'),
            'COF-SIG-001' => $this->drink('Café Mocha', 'كافيه موكا', 10500, ['hot', 'signature'], 'Espresso, chocolate, and steamed milk.', 'إسبريسو وشوكولاتة وحليب مبخر.'),
            'COF-ICE-001' => $this->drink('Iced Americano', 'آيس أمريكانو', 7500, ['iced'], 'Bold espresso, cold water, and ice.', 'إسبريسو قوي مع ماء بارد وثلج.'),
            'COF-ICE-002' => $this->drink('Iced Latte', 'آيس لاتيه', 9500, ['iced'], 'Espresso and cold milk poured over ice.', 'إسبريسو وحليب بارد يقدم مع الثلج.', true),
            'COF-ICE-003' => $this->drink('Cold Brew', 'كولد برو', 11000, ['iced'], 'Slow-steeped coffee with a naturally smooth finish.', 'قهوة منقوعة ببطء بطعم ناعم طبيعي.'),
            'COF-SIG-002' => $this->drink('Spanish Latte', 'سبانيش لاتيه', 11500, ['signature'], 'Espresso with creamy milk and a touch of sweetness.', 'إسبريسو مع حليب كريمي ولمسة حلاوة.', true),
            'COF-SIG-003' => $this->drink('Caramel Macchiato', 'كراميل ماكياتو', 12000, ['signature'], 'Vanilla milk, espresso, and caramel drizzle.', 'حليب بالفانيليا وإسبريسو وصوص الكراميل.'),
        ];
    }

    /** @return array<string, mixed> */
    private function category(string $slug, int $sortOrder, string $enName, string $arName, string $enDescription, string $arDescription): array
    {
        return [
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'en' => ['name' => $enName, 'description' => $enDescription],
            'ar' => ['name' => $arName, 'description' => $arDescription],
        ];
    }

    /** @return array<string, mixed> */
    private function drink(string $enName, string $arName, int $price, array $categories, string $enDescription, string $arDescription, bool $isFeatured = false): array
    {
        return [
            'price' => $price,
            'categories' => $categories,
            'is_featured' => $isFeatured,
            'en' => ['name' => $enName, 'short_description' => $enDescription, 'description' => $enDescription],
            'ar' => ['name' => $arName, 'short_description' => $arDescription, 'description' => $arDescription],
        ];
    }

    /** @param list<string> $categories */
    private function imageFor(array $categories): string
    {
        return match (true) {
            in_array('signature', $categories, true) => 'signature-coffee.png',
            in_array('iced', $categories, true) => 'iced-coffee.png',
            in_array('hot', $categories, true) => 'hot-coffee.png',
            default => 'espresso.png',
        };
    }

    private function attachImage(Category|Product $model, string $filename, string $collection): void
    {
        if ($model->hasMedia($collection)) {
            return;
        }

        $model
            ->addMedia(database_path("seeders/assets/coffee/{$filename}"))
            ->preservingOriginal()
            ->toMediaCollection($collection);
    }
}
