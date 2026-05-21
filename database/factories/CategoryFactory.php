<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * categoryのファクトリー
 */
class CategoryFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->word(),  // カテゴリー名をランダムな文字列で生成
        ];
    }
}
