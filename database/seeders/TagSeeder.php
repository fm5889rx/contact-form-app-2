<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 固定データ1件目の登録
        Tag::create([
            'name' => '質問',
        ]);

        // 固定データ2件目の登録
        Tag::create([
            'name' => '要望',
        ]);

        // 固定データ3件目の登録
        Tag::create([
            'name' => '不具合報告',
        ]);

        // 固定データ4件目の登録
        Tag::create([
            'name' => 'ご意見',
        ]);

        // 固定データ5件目の登録
        Tag::create([
            'name' => 'その他',
        ]);
    }
}
