<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ArabicValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('ar');
    }

    public function test_standard_validation_messages_are_returned_in_arabic(): void
    {
        $validator = Validator::make([
            'email' => 'not-an-email',
            'price' => 'not-a-number',
        ], [
            'customer_name' => ['required'],
            'email' => ['email'],
            'price' => ['numeric'],
        ]);

        $this->assertSame('حقل اسم العميل مطلوب.', $validator->errors()->first('customer_name'));
        $this->assertSame('يجب أن يكون البريد الإلكتروني بريدًا إلكترونيًا صالحًا.', $validator->errors()->first('email'));
        $this->assertSame('يجب أن يكون السعر رقمًا.', $validator->errors()->first('price'));
    }

    public function test_translated_attributes_and_unique_rules_use_arabic_messages(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $validator = Validator::make([
            'email' => 'existing@example.com',
            'ar' => [],
        ], [
            'email' => ['unique:users,email'],
            'ar.name' => ['required'],
        ]);

        $this->assertSame('قيمة البريد الإلكتروني مستخدمة من قبل.', $validator->errors()->first('email'));
        $this->assertSame('حقل الاسم بالعربية مطلوب.', $validator->errors()->first('ar.name'));
        $this->assertSame('قيمة الاسم بالعربية مستخدمة من قبل.', __('validation.translatableUnique', [
            'attribute' => 'الاسم بالعربية',
        ]));
    }
}
