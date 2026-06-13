<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;                // データベースをリフレッシュするトレイト

    protected $categoryId;              // category_idを保持しておく変数
    protected $tagId;                   // tag_idを保持しておく変数
    protected $contactId;               // contact_idを保持しておく変数

    public function setUp(): void
    {
        parent::setUp();

        //テストで使用するテーブルを事前に作成
        $category = Category::create([      // テスト用のCategoryテーブルを作成
            'id'        => 1,
            'content'   => 'Test Category',
        ]);
        $this->categoryId = $category->id;  // category_idを保持

        $tag = Tag::factory()->create([     // テスト用のtagテーブルを作成
            'id'        => 1,
        ]); 
        $this->tagId = $tag->id;            // tag_idを保持

        $contact = Contact::factory()->create([     // テスト用のcontactテーブルを作成
            'category_id' => $this->categoryId,
        ]);
        $this->contactId = $contact->id;            // contact_idを保持
    }
    /**
     * お問い合わせフォームの表示テスト
     */
    public function test_contact_form_page_display(): void
    {
        $response = $this->get('/');    // お問い合わせフォームのURLにGETリクエストを送信

        $response->assertStatus(200);   // HTTPステータスコード200を期待
    }

    /**------------------------------------------------------------------------
     * お問い合わせ入力フォーム系のテスト
     *-----------------------------------------------------------------------*/
    /**
     * お問い合わせフォームの送信テスト（正常系）
     */
    public function test_contact_form_confirm_submission(): void
    {
        $response = $this->post('/contacts/confirm', [   // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302)   // HTTPステータスコード302を期待（リダイレクト）
                ->assertRedirect('/');  // 入力フォームにリダイレクト
    }

    /**
     * お問い合わせフォームの送信テスト（異常系）
     */
    public function test_contact_form_with_invalid_data(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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
    public function test_contact_form_with_invalid_category_id(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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
    public function test_contact_form_with_invalid_email(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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
    public function test_contact_form_with_invalid_tel(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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
    public function test_contact_form_with_missing_required_fields(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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
    public function test_contact_form_with_all_valid_data(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
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

    /**
     * お問い合わせフォームの送信テスト（住所の異常値）
     */
    public function test_contact_form_with_invalid_address(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '',                 // 住所が空
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['address']);
    }

    /**
     * お問い合わせフォームの送信テスト（建物名の異常値）
     */
    public function test_contact_form_with_invalid_building(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'building'    => str_repeat('A',256),   // 建物名が256文字（空はエラーにならない0
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['building']);
    }

    /**
     * お問い合わせフォームの送信テスト（詳細の異常値）
     */
    public function test_contact_form_with_invalid_detail(): void
    {
        $response = $this->post('/contacts/confirm', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => '',                // 詳細が空
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['detail']);
    }

    /**------------------------------------------------------------------------
     * お問い合わせ詳細系のテスト
     *-----------------------------------------------------------------------*/

    /**
     * お問い合わせ詳細の送信テスト（正常系）
     */
    public function test_contact_form_thanks_submission(): void
    {
        $response = $this->post('/contacts', [   // お問い合わせ確認のURLにPOSTリクエストを送信
            'category_id' => $this->categoryId,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お問い合わせ詳細',
        ]);
        // contact_tagピボットテーブルを更新
        $contact = Contact::where('email', 'user@example.com')->first();
        $this->assertNotNull($contact, '作成後にレコードが見つかりません');
        $contact->tags()->attach($this->tagId);      // ピボットテーブルを作成

        // 作成されたcontactレコードの比較
        $this->assertDatabaseHas('contacts', [
            'category_id' => $this->categoryId,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'building'    => NULL,                  // buildingはnullable
            'detail'      => 'お問い合わせ詳細',
        ]);

        // ピボットテーブルの比較
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id'     => $this->tagId,
        ]);

        // 画面推移の検証
        $response->assertStatus(302)   // HTTPステータスコード302を期待（リダイレクト）
            ->assertRedirect('/thanks');  // サンクス画面にリダイレクト
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

    /**
     * お問い合わせフォームの送信テスト（住所の異常値）
     */
    public function test_contact_form_submission_with_invalid_address(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '',                 // 住所が空
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['address']);
    }

    /**
     * お問い合わせフォームの送信テスト（建物名の異常値）
     */
    public function test_contact_form_submission_with_invalid_building(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'building'    => str_repeat('A', 256),   // 建物名が256文字（空はエラーにならない0
            'detail'      => 'お問い合わせ詳細',
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['building']);
    }

    /**
     * お問い合わせフォームの送信テスト（詳細の異常値）
     */
    public function test_contact_form_submission_with_invalid_detail(): void
    {
        $response = $this->post('/contacts', [  // お問い合わせフォームのURLにPOSTリクエストを送信
            'category_id' => 1,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'user@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => '',                // 詳細が空
        ]);

        $response->assertStatus(302);   // HTTPステータスコード302を期待（リダイレクト）

        // セッションにエラーメッセージが存在することを期待
        $response->assertSessionHasErrors(['detail']);
    }
}
