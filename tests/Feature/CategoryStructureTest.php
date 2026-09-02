<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CategoryStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_hierarchical_translatable_and_related_to_products(): void
    {
        $parent = Category::create([
            'status' => 'active',
            'en' => ['name' => 'Coffee'],
            'ar' => ['name' => 'القهوة'],
        ]);
        $child = Category::create([
            'parent_id' => $parent->id,
            'status' => 'active',
            'en' => ['name' => 'Coffee Beans'],
            'ar' => ['name' => 'حبوب القهوة'],
        ]);
        $product = Product::create([
            'sku' => 'BEANS-1',
            'price' => 10_000,
            'en' => ['name' => 'House Blend'],
            'ar' => ['name' => 'خلطة الدار'],
        ]);

        $product->categories()->attach($child);

        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($product->categories->contains($child));
        $this->assertTrue($child->products->contains($product));
        $this->assertSame('coffee-beans', $child->translate('en')->slug);
        $this->assertSame('حبوب-القهوة', $child->translate('ar')->slug);
    }

    public function test_category_hierarchy_rejects_cycles(): void
    {
        $parent = Category::create(['en' => ['name' => 'Parent']]);
        $child = Category::create([
            'parent_id' => $parent->id,
            'en' => ['name' => 'Child'],
        ]);

        $this->expectException(ValidationException::class);

        $parent->update(['parent_id' => $child->id]);
    }
}
