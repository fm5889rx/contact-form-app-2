<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ContactFormTest extends TestCase
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

    /**
     * １つのタグが中間テーブルを介して複数のお問い合わせと紐付けされることを確認するテスト
     */
    public function test_a_tag_can_be_associated_with_multiple_contacts()
    {
        // ダミーのTag、CategoryとContactを作成
        $tag = Tag::create([
            'name' => 'Test tag'
        ]);
        $category = Category::create([
            'content' => 'Tast Category'
        ]);
        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id]
        );

        // 中間テーブルに関連付け
        $tag->contacts()->attach($contacts->pluck('id')->toArray());

        // データベース側で中間テーブルが作られたか確認
        $this->assertDatabaseCount('contact_tag', 3);

        // モデル経由で取得したcontactsが3件あるか
        $this->assertCount(3, $tag->contacts);

        // 取得したIDが期待通りかを確認
        $tagContactIds = $tag->contacts->pluck('id')->toArray();
        $expectedIds   = $contacts->pluck('id')->toArray();
        $this->assertEqualsCanonicalizing($expectedIds, $tagContactIds);
    }

    /**
     * １つのお問い合わせが特定のカテゴリーに属し、複数のタグ同期できることを検証するテスト
     */
    public function test_contact_belongs_to_one_category_and_can_sync_tags()
    {
        /* ------------------------------------------------------------------
         * モデル作成
         * ------------------------------------------------------------------ */
        $category = Category::create([                      // テスト用のカテゴリを作成
            'content' => 'Test Category'
        ]);

        $tags = Tag::factory()->count(3)->create();         // 3件のユニークなtagを生成

        $contact = Contact::factory()->create([
            'category_id' => $category->id,                 // カテゴリを紐づけ
        ]);

        /* ------------------------------------------------------------------
         * tagsの同期
         * ------------------------------------------------------------------ */
        $contact->tags()->sync(collect($tags)->pluck('id')->toArray());

        /* ------------------------------------------------------------------
         * リレーションの確認
         * ------------------------------------------------------------------ */
        // belongsTo の確認
        $this->assertEquals($category->id, $contact->category->id);

        // 同期したタグが中間テーブルにあるか
        foreach ($tags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id'     => $tag->id,
            ]);
        }

        // tagsから取得したサイズが期待通りか
        $this->assertCount(3, $contact->tags);

        /* ------------------------------------------------------------------
         * tagsを差し替えて再同期
         * ------------------------------------------------------------------ */
        $newTags = Tag::factory()->count(2)->create();
        $contact->tags()->sync($newTags->pluck('id')->toArray());

        // 先に同期した 3 つのタグは削除されている
        foreach ($tags as $tag) {
            $this->assertDatabaseMissing('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id'     => $tag->id,
            ]);
        }

        // 新しい 2 つのタグだけ残っている
        foreach ($newTags as $tag) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id'     => $tag->id,
            ]);
        }

        // tags コレクションのサイズは 2 になる
        $this->assertCount(2, $contact->fresh()->tags);
    }
}

