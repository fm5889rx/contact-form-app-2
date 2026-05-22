<?php

namespace App\Models;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'content',
    ];
    
    /**
     * カテゴリーのバリデーションルール
     */
    public static $rules = [
        'content' => 'required|max:255|unique:categories,content',
    ];

    /**
     * このタグを使用する複数のコンテンツを取得
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
