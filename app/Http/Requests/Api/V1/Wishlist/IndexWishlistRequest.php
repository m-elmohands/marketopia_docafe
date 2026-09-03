<?php

namespace App\Http\Requests\Api\V1\Wishlist;

use App\Models\WishlistItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexWishlistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', WishlistItem::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in(['newest', 'oldest'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
