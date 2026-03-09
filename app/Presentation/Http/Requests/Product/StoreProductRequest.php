<?php

namespace App\Presentation\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:190'],
            'sku' => ['required','string','max:80'],
            'price' => ['required','numeric','min:0'],
            'stock' => ['required','integer','min:0'],
            'description' => ['nullable','string'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048']
        ];
    }
}