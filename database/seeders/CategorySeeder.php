<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 固定データ1件目の登録
        Category::create([
            'content' => '商品のお届けについて',
        ]);

        // 固定データ2件目の登録
        Category::create([
            'content' => '商品の交換について',
        ]);

        // 固定データ3件目の登録
        Category::create([
            'content' => '商品トラブル',
        ]);

        // 固定データ4件目の登録
        Category::create([
            'content' => 'ショップへのお問い合わせ',
        ]);

        // 固定データ5件目の登録
        Category::create([
            'content' => 'その他',
        ]);
    }
}
