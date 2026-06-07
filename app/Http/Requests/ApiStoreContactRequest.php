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
            'tag_ids' => 'nullable|array|exists:tags,id',
            'tag_ids.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => '姓は文字列で入力してください',
            'first_name.max' => '姓は255文字以内で入力してください',
            'last_name.string' => '名は文字列で入力してください',
            'last_name.max' => '名は255文字以内で入力してください',
            'gender.integer' => '性別は整数で入力してください',
            'email.string' => 'メールアドレスは文字列で入力してください',
            'email.email' => 'メールアドレスは有効なメールアドレス形式で入力してください',
            'email.max' => 'メールアドレスは255文字以内で入力してください',
            'tel.string' => '電話番号は文字列で入力してください',
            'tel.regex' => '電話番号は10桁または11桁の数字で入力してください',
            'address.string' => '住所は文字列で入力してください',
            'address.max' => '住所は255文字以内で入力してください',
            'building.string' => '建物名は文字列で入力してください',
            'building.max' => '建物名は255文字以内で入力してください',
            'category_id.integer' => 'カテゴリーIDは整数で入力してください',
            'category_id.exists' => '指定されたカテゴリーIDは存在しません',
            'detail.string' => '内容は文字列で入力してください',
            'detail.max' => '内容は120文字以内で入力してください',
            'tag_ids.array' => 'タグIDは配列で入力してください',
            'tag_ids.exists' => '指定されたタグIDは存在しません',
            'tag_ids.*.exists' => '指定されたタグIDは存在しません',
        ];
    }
}
