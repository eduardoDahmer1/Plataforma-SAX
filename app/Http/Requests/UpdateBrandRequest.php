<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:brands,name,' . $this->brand->id,
            'slug' => 'required|unique:brands,slug,' . $this->brand->id,
        ];
    }
}
