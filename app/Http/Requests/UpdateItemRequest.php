<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => [
                'string',
                Rule::unique('items')->ignore($this->item),
            ],
            'name' => ['string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['numeric', 'min:0'],
            'stock' => ['integer', 'min:0'],
            'status' => ['boolean'],
            'category_id' => ['integer', 'exists:categories,id'],
        ];
    }
}
