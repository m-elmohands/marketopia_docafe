<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EnglishValidationTest extends TestCase
{
    public function test_ecommerce_and_translated_attributes_have_readable_english_messages(): void
    {
        app()->setLocale('en');

        $validator = Validator::make([
            'ar' => [],
        ], [
            'customer_name' => ['required'],
            'ar.name' => ['required'],
        ]);

        $this->assertSame('The customer name field is required.', $validator->errors()->first('customer_name'));
        $this->assertSame('The Arabic name field is required.', $validator->errors()->first('ar.name'));
        $this->assertSame('The Arabic name value is already in use.', __('validation.translatableUnique', [
            'attribute' => 'Arabic name',
        ]));
    }
}
