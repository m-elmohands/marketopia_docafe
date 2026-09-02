<?php

namespace Tests\Unit;

use App\Filament\Support\LocalizedRecordLabel;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalizedRecordLabelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_falls_back_to_an_available_translation(): void
    {
        app()->setLocale('en');
        $category = new Category;
        $category->setAttribute('id', 15);
        $category->translateOrNew('ar')->name = 'قهوة ساخنة';

        $this->assertSame('قهوة ساخنة', LocalizedRecordLabel::get($category, 'name'));
    }

    #[Test]
    public function it_always_returns_a_string_when_no_translation_exists(): void
    {
        $category = new Category;
        $category->setAttribute('id', 15);

        $this->assertSame('Untitled record #15', LocalizedRecordLabel::get($category, 'name'));
    }
}
