<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiIndexContactRequest extends FormRequest
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
            'keyword' => 'nullable|string|max:255',
            'gender' => 'nullable|integer|in:1,2,3',
            'category_id' => 'nullable|integer|exists:categories,id',
            'date' => 'nullable|date',
            'tag_id' => 'nullable|integer|exists:tags,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => 'キーワードは文字列で入力してください',
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'gender.integer' => '性別は整数で入力してください',
            'gender.in' => '性別は1（男性）、2（女性）、3（その他）から選択してください',
            'category_id.integer' => 'カテゴリーIDは整数で入力してください',
            'category_id.exists' => '指定されたカテゴリーIDは存在しません',
            'date.date' => '日付は有効な日付形式で入力してください',
            'tag_id.integer' => 'タグIDは整数で入力してください',
            'tag_id.exists' => '指定されたタグIDは存在しません',
            'per_page.integer' => '1ページあたりの表示件数は整数で入力してください',
            'per_page.min' => '1ページあたりの表示件数は1以上で入力してください',
            'per_page.max' => '1ページあたりの表示件数は100以下で入力してください',
            'page.integer' => 'ページ番号は整数で入力してください',
            'page.min' => 'ページ番号は1以上で入力してください',
        ];
    }
}
