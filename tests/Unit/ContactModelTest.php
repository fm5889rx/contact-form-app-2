<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;   // データベースをリフレッシュするトレイト
    

    public function test_contact_relation(): void
    {
        // カテゴリーのダミーデータを作成
        $category = Category::factory()->create();

        // お問い合わせのダミーデータを作成
        $contact = Contact::factory()->create([
            'category_id' => $category->id,  // カテゴリーIDを関連付ける
        ]);

        /*
         * お問い合わせに関連するカテゴリーが存在することを確認
         */
        $this->assertNotEmpty($contact->category, 'お問い合わせに関連するカテゴリーが存在します。');
        
        /*
         * お問い合わせに関連するカテゴリーの内容を確認
         */
        $this->assertEquals($category->id, $contact->category->id, 'お問い合わせに関連するカテゴリーのIDが正しいことを確認します。');
    }
}
