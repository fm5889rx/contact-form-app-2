<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:tags,name',
        ];
    }

    /**
     * バリデーションエラー時のメッセージ（日本語）
     */
    public function messages()
    {
        return [
            'name.required' => 'タグ名を入力してください',
            'name.unique' => '既にそのタグ名は使われています',
        ];
    }
}
