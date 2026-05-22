<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * contactのファクトリー
 */
class ContactFactory extends Factory
{
    /**
     * モデルのデフォルト状態を定義する
     */
    public function definition(): array
    {
        return [
            'category_id' => $this->faker->numberBetween(1, 5), // category_idを1から5の範囲でランダムに生成
            'first_name' => $this->faker->firstName(),          // first_nameをランダムな名前で生成
            'last_name' => $this->faker->lastName(),            // last_nameをランダムな名前で生成
            'gender' => $this->faker->numberBetween(1, 3),      // 性別を1から3の範囲でランダムに生成
            'email' => $this->faker->unique()->safeEmail(),     // emailをユニークな安全なメールアドレスで生成
            'tel' => $this->faker->regexify('^0\d{9,10}$'),     // telを0から始まる10桁または11桁の数字で生成
            'address' => $this->faker->address,                 // 住所をランダムな文字列で生成
            'building' => $this->faker->secondaryAddress,       // 建物名をランダムな文字列で生成
            'detail' => $this->faker->text(random_int(10, 120)),// detailを10文字から120文字のランダムなテキストで生成
        ];
    }
}
