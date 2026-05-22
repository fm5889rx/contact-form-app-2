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
        $category = Category::create([
            'content' => 'Test Category',
        ]);

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

    /*
     * お問い合わせのカテゴリーが削除された場合のテスト
     */
    public function test_contact_relation_with_deleted_category(): void
    {
        // カテゴリーのダミーデータを作成
        $category = Category::create([
            'content' => 'Test Category',
        ]);

        // お問い合わせのダミーデータを作成
        $contact = Contact::factory()->create([
            'category_id' => $category->id,  // カテゴリーIDを関連付ける
        ]);

        // カテゴリーを削除
        $category->delete();

        /*
         * お問い合わせに関連するカテゴリーが存在しないことを確認
         */
        $this->assertEmpty($contact->category, 'お問い合わせに関連するカテゴリーが存在しません。');
    }

    /*
     * お問い合わせのカテゴリーが複数存在する場合のテスト
     */
    public function test_contact_relation_with_multiple_categories(): void
    {
        // カテゴリーのダミーデータを複数作成
        $category1 = Category::create([
            'content' => 'Test Category 1',
        ]);
        $category2 = Category::create([
            'content' => 'Test Category 2',
        ]);

        // お問い合わせのダミーデータを作成（最初のカテゴリーIDを関連付ける）
        $contact = Contact::factory()->create([
            'category_id' => $category1->id,  // 最初のカテゴリーIDを関連付ける
        ]);
        // お問い合わせのカテゴリーIDを2番目のカテゴリーIDに更新
        $contact->category_id = $category2->id;
        $contact->save();   // お問い合わせのカテゴリーIDを更新して保存

        /*
         * お問い合わせに関連するカテゴリーが存在することを確認
         */
        $this->assertNotEmpty($contact->category, 'お問い合わせに関連するカテゴリーが存在します。');
        /*
         * お問い合わせに関連するカテゴリーの内容を確認
         */
        $this->assertEquals($category2->id, $contact->category->id, 'お問い合わせに関連するカテゴリーのIDが正しいことを確認します。');
    }

    /*
     * お問い合わせのカテゴリーが正しく関連付けられていることを確認するテスト
     */
    public function test_contact_relation_with_valid_category(): void
    {
        // カテゴリーのダミーデータを作成
        $category = Category::create([
            'content' => 'Test Category',
        ]);

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
