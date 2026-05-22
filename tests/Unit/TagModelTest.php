<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * タグとお問い合わせのリレーションテスト
     */
    public function test_tag_contacts_relation(): void
    {
        $tag = Tag::create([                        // タグのダミーデータを作成

            'name' => 'Test Tag',
        ]);

        $category = Category::create([              // ダミーのカテゴリーを作成
            'content' => 'Test Category',
        ]);

        // お問い合わせのダミーデータ作成
        $contact = Contact::factory()->create([
            'category_id' => $category->id,         // カテゴリーIDを関連付ける
        ]);

        // タグとお問い合わせの関連付け
        $tag->contacts()->attach($contact->id);     // タグとお問い合わせを関連付ける

        // タグから関連するお問い合わせを取得
        $relatedContacts = $tag->contacts;          // タグから関連するお問い合わせを取得

        // 関連するお問い合わせが正しいかを確認
        $this->assertTrue($relatedContacts->contains($contact), 'タグに関連するお問い合わせが正しく関連付けられています。');
    }
}
