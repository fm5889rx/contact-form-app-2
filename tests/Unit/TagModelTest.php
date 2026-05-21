<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tag;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * タグとお問い合わせのリレーションテスト
     */
    public function test_tag_contacts_relation(): void
    {
        // タグの作成
        $tag = Tag::factory()->create();    // タグのダミーデータを作成

        // お問い合わせの作成
        $contact = Contact::factory()->create();    // お問い合わせのダミーデータを作成

        // タグとお問い合わせの関連付け
        $tag->contacts()->attach($contact->id);    // タグとお問い合わせを関連付ける

        // タグから関連するお問い合わせを取得
        $relatedContacts = $tag->contacts;    // タグから関連するお問い合わせを取得

        // 関連するお問い合わせが正しいかを確認
        $this->assertTrue($relatedContacts->contains($contact), 'タグに関連するお問い合わせが正しく関連付けられています。');
    }
}
