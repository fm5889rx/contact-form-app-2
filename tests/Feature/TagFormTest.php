<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagFormTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * タグの追加テスト
     */
    public function test_tag_form_add_tag(): void
    {
        $admin = User::factory()->create();     // 管理者ユーザーを作成

        $this->actingAs($admin);                // 管理者ユーザーで認証

        // タグを保存する処理を実行
        $response = $this->post('/admin/tags', [
            'content' => 'タグ1',
        ]);

        // HTTPステータスコード302を期待（リダイレクト）
        $response->assertStatus(302);
    }

    /**
     * タグの追加テスト（未認証ユーザー）
     */
    public function test_tag_form_add_tag_unauthenticated(): void
    {
        // タグを保存する処理を実行
        $response = $this->post('/admin/tags', [
            'name' => 'タグ1',
        ]);

        // HTTPステータスコード302を期待（リダイレクト）
        $response->assertStatus(302);

        // ログインページにリダイレクトされることを期待
        $response->assertRedirect('/login');   // ログインページにリダイレクトされることを期待

        // セッションにエラーメッセージが存在しないことを期待
        $response->assertSessionMissing('success');
    }

    /**
     * タグの編集テスト
     */
    public function test_tag_form_edit_tag(): void
    {
        $admin = User::factory()->create();     // 管理者ユーザーを作成

        $this->actingAs($admin);                // 管理者ユーザーで認証

        // タグを保存する処理を実行
        $response = $this->post('/admin/tags', [
            'name' => 'タグ1',
        ]);

        // タグ編集画面に推移する処理を実行
        $response = $this->get('/admin/tags/1/edit');  // タグ編集ページのURLにGETリクエストを送信

        // HTTPステータスコード404を期待（リダイレクトしない）
        $response->assertStatus(404);
    }

    /**
     * タグの編集テスト（未認証ユーザー）
     */
    public function test_tag_form_edit_tag_unauthenticated(): void
    {
        // タグ編集画面に推移する処理を実行
        $response = $this->get('/admin/tags/1/edit');  // タグ編集ページのURLにGETリクエストを送信

        // HTTPステータスコード201を期待（リダイレクト）
        $response->assertStatus(302);

        // ログインページにリダイレクトされることを期待
        $response->assertRedirect('/login');   // ログインページにリダイレクトされることを期待
    }

    /**
     * タグの削除テスト
     */
    public function test_tag_form_delete_tag(): void
    {
        $admin = User::factory()->create();     // 管理者ユーザーを作成

        $this->actingAs($admin);                // 管理者ユーザーで認証

        // タグを保存する処理を実行
        $response = $this->post('/admin/tags', [
            'name' => 'タグ1',
        ]);

        // タグ削除処理を実行
        $response = $this->delete('/admin/tags/1');  // タグ削除ページのURLにDELETEリクエストを送信

        // HTTPステータスコード404を期待（リダイレクトされない）
        $response->assertStatus(404);
    }

    /**
     * タグの削除テスト（未認証ユーザー）
     */
    public function test_tag_form_delete_tag_unauthenticated(): void
    {
        // タグ削除処理を実行
        $response = $this->delete('/admin/tags/1');  // タグ削除ページのURLにDELETEリクエストを送信

        // HTTPステータスコード302を期待（リダイレクト）
        $response->assertStatus(302);

        // ログインページにリダイレクトされることを期待
        $response->assertRedirect('/login');   // ログインページにリダイレクトされることを期待

        // セッションにエラーメッセージが存在しないことを期待
        $response->assertSessionMissing('success');
    }
}
