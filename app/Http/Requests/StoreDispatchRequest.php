<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class StoreDispatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'staff_id' => ['required', 'exists:staff,id'],
            'quantity' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $item = Item::find(request()->get('item_id'));
                    if ($item === null) {
                        $fail('The selected item does not exist.');
                    } elseif ($item->stock < $value) {
                        $fail('The quantity must not be greater than the available stock of the item.');
                    }

                },
            ],
        ];
    }
}
