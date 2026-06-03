<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiStoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'gender' => 'integer|in:1,2,3',
            'email' => 'string|email|max:255',
            'tel' => 'string|regex:/^[0-9]{10,11}$/',
            'address' => 'string|max:255',
            'building' => 'nullable|string|max:255',
            'category_id' => 'integer|exists:categories,id',
            'detail' => 'string|max:120',
            'tag_ids' => 'nullable|array|integer|exists:tags,id',
        ];
    }
}
