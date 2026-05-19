<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Contact;

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
     * このタグを使用する複数のコンテンツを取得
     */
    public function contacts(): HasMany
    {
        return $this->HasMany(Contact::class);
    }
}
