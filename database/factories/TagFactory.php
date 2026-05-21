<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(), // タグ名をランダムな単語で生成
        ];
    }
}
