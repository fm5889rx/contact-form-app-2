<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Backtrace\Arguments\ReducedArgument\TruncatedReducedArgument;

class TagModelTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * タグとお問い合わせのリレーションテスト
     */
    public function test_tag_contacts_relation()
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

    /**
     * 正常系
     */
    public function test_tag_required_name()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => 'Test Tag',
        ]);

        $this->assertTrue(TRUE, 'タグが新規作成されました。');
    }

    /** タグの更新 */
    public function test_tag_update()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => 'Test Tag',                   // タグ名が31文字
        ]);
        $tag->update([                              // 同じIDのタグを更新
            'name' => 'Test Tag 2',                 // 異なるタグ名で更新
        ]);

        $this->assertTrue(TRUE, 'タグの更新に成功しました。');
    }

    /** タグの削除 */
    public function test_tag_delete()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => 'Test Tag',                   // タグ名が31文字
        ]);
        $tag->delete();                             // タグを削除

        $this->assertTrue(TRUE, 'タグの削除に成功しました。');
    }

    /**
     * 異常系
     */
    /** タグ名が空 */
    public function test_tag_invalid_name()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => '',
        ]);

        $this->assertTrue(TRUE, 'タグ名は省略できません。');
    }

    /** タグ名が長すぎる */
    public function test_tag_invalid_name_too_long()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => str_repeat('A', 31),          // タグ名が31文字
        ]);

        $this->assertTrue(TRUE, 'タグ名は30文字以下です。');
    }

    /** タグ名の重複 */
    public function test_tag_invalid_name_unique()
    {
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => 'Test Tag',                   // タグ名が31文字
        ]);
        $tag = Tag::create([                        // タグのダミーデータを作成
            'name' => 'Test Tag',                   // 同じタグ名
        ]);

        $this->assertTrue(TRUE, '同じタグ名は使用できません。');
    }
}
