<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    /**
     * お問い合わせフォームの表示テスト
     */
    public function test_contact_form_page_display(): void
    {
        $response = $this->get('/');    // お問い合わせフォームのURLにGETリクエストを送信

        $response->assertStatus(200);   // HTTPステータスコード200を期待
    }

    /**
     * お問い合わせフォームの送信テスト（正常系）
     */
    public function test_contact_form_submission(): void
    {
        $response = $this->post('/contacts', [   // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）
    }

    /**
     * お問い合わせフォームの送信テスト（異常系）
     */
    public function test_contact_form_submission_with_invalid_data(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => '',                // カテゴリーIDが空
            'first_name'  => '',                // 名前（姓）が空
            'last_name'   => '',                // 名前（名）が空
            'gender'      => '',                // 性別が空
            'email'       => 'invalid-email',   // メールアドレスが不正
            'tel'         => 'invalid-tel',     // 電話番号が不正
            'address'     => '',                // 住所が空
            'detail'      => '',                // お問い合わせ詳細が空
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }

    /**
     * お問い合わせフォームの送信テスト（カテゴリーIDの異常値）
     */
    public function test_contact_form_submission_with_invalid_category_id(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 6,                // カテゴリーIDは1〜5の範囲である必要があるため異常値
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['category_id']);
    }

    /**
     * お問い合わせフォームの送信テスト（メールアドレスの異常値）
     */
    public function test_contact_form_submission_with_invalid_email(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'invalid-email',   // メールアドレスが不正
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * お問い合わせフォームの送信テスト（電話番号の異常値）
     */
    public function test_contact_form_submission_with_invalid_tel(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => 'invalid-tel',     // 電話番号が不正
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['tel']);
    }

    /**
     * お問い合わせフォームの送信テスト（必須項目の異常値）
     */
    public function test_contact_form_submission_with_missing_required_fields(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => '',                // カテゴリーIDが空
            'first_name'  => '',                // 名前（姓）が空
            'last_name'   => '',                // 名前（名）が空
            'gender'      => '',                // 性別が空
            'email'       => '',                // メールアドレスが空
            'tel'         => '',                // 電話番号が空
            'address'     => '',                // 住所が空
            'detail'      => '',                // お問い合わせ詳細が空
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }

    /**
     * お問い合わせフォームの送信テスト（性別値が異常値）
     */
    public function test_contact_form_submission_with_all_valid_data(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 4,             // 性別は1〜3の範囲である必要があるため異常値
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['gender']);
    }
}
