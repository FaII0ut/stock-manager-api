<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'numeric', 'min:0'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['string', 'max:255'],
            'price' => ['numeric', 'min:0'],
            'stock' => ['numeric', 'min:0'],
            'status' => ['boolean', 'min:0'],
        ];
    }
}
