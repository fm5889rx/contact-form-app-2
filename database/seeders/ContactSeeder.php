<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Tag;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * お問い合わせデータを20件、ダミーデータで投入する
         */
        for ($i = 0; $i < 20; $i++) {
            // お問い合わせデータをContactFactoryで指定した形で保存
            $contact = Contact::factory()->create();

            // Tag_idをランダムに発生
            $tagIds = Tag::inRandomOrder()->first();
            // 中間テーブルにtag_idを保存
            $contact->tags()->attach($tagIds);
        }

    }
}
