<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * 管理者ページの表示テスト
     */
    public function test_admin_page_display(): void
    {
        $admin = User::factory()->create();     // 管理者ユーザーを作成

        $this->actingAs($admin);                // 管理者ユーザーで認証

        $response = $this->get('/admin');       // 管理者ページのURLにGETリクエストを送信

        $response->assertStatus(200);           // HTTPステータスコード200を期待
    }

    /**
     * 管理者ページの送信テスト（正常系）
     */
    public function test_admin_page_submission(): void
    {
        $admin = User::factory()->create();     // 管理者ユーザーを作成

        $this->actingAs($admin);                // 管理者ユーザーで認証

        // カテゴリーを作成して保存する
        $category = Category::create([
            'content' => 'カテゴリー1',
        ]);

        // お問い合わせを作成して保存する
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        // お問い合わせを保存する処理を実行
        $contact->save();

        // お問い合わせ詳細画面に推移する処理を実行
        $response = $this->get('/admin/contacts/{$contact->id}');  // 管理者ページのURLにGETリクエストを送信

        // HTTPステータスコード404を期待（リダイレクトされない）
        $response->assertStatus(404);
    }

    /**
     * 管理者ページの送信テスト（全ての正常値の未認証ユーザー）
     */
    public function test_admin_page_submission_with_all_valid_data_and_unauthenticated_user(): void
    {
        $response = $this->post('/admin/contacts', [  // 管理者ページのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        // HTTPステータスコード404を期待（リダイレクトされない）
        $response->assertStatus(404);
    }

    /**
     * 管理者ページの送信テスト（全ての正常値の認証ユーザー）
     */
    public function test_admin_page_submission_with_all_valid_data_and_authenticated_user(): void
    {
        // ユーザーを作成して認証する
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/admin/contacts', [  // 管理者ページのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        // HTTPステータスコード404を期待（リダイレクトされない）
        $response->assertStatus(404);
    }
}