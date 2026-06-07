<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;                        // データベースをリフレッシュするトレイト
    use MakesHttpRequests;                      // HTTP系メソッドを提供するトレイト

    /* @var int */
    protected $categoryId;                      // テスト用に保持するcategory_id
    protected $tagId;                           // テスト用に保持するtag_id

    public function setUp(): void
    {
        parent::setUp();

        // テスト用のcategoryデータを作成
        $category = Category::create([          // category_idが存在することを保証
            'id' => 1,
            'content' => 'テストカテゴリ'
        ]);
        $this->categoryId = $category->id;      // 取得したIDを保持

        // テスト用のtagsテーブルを作成
        $tag = Tag::create([                    // tag_idが存在することを保証
            'id' => 1,
            'name' => 'テストタグ',
        ]);
        $this->tagId = $tag->id;                // 取得したIDを保持

        // Contact モデルのファクトリーを使用してテストデータを作成
        Contact::factory()->create([
            'category_id' => $category->id,     // 存在する category_id を使用
        ]);
    }

    /**
     * GET系テスト
     */
    /** @test */
    public function test_api_accepts_valid_query_parameters()
    {
        // API に対して有効なクエリパラメータを送信
        $response = $this->getJson('/api/v1/contacts', [
            'keyword'      => '',   // 検索対象では無い
            'gender'       => 1,    // 存在する gender を使用
            'category_id'  => 1,    // この ID が categories テーブルに存在することを想定
            'date'         => '',   // 検索対象ではない
            'per_page'     => 7,
            'page'         => 1,
        ]);

        $response->assertSuccessful();              // 200 系レスポンスを期待

        $response->assertJsonStructure([            // 必要に応じて返却データ構造を確認
            'data',
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function test_api_rejects_invalid_gender_value()
    {
        // gender は in:1,2,3 なので、文字列や数値以外の値を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'gender' => 'invalid',   // 文字列・数値以外
        ]);

        $response
            ->assertStatus(422)                        // バリデーション失敗時は 422
            ->assertJsonValidationErrors(['gender']);  // genderにエラーがあることを保証
    }

    /** @test */
    public function test_api_rejects_gender_out_of_range()
    {
        // gender は in:1,2,3 なので、範囲外の値を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'gender' => 99,   // in:1,2,3 の範囲外
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function test_api_rejects_non_existent_category_id()
    {
        // 存在しない category_id を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'category_id' => 99999,   // 存在しない ID
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function test_api_rejects_invalid_date_format()
    {
        //
        $response = $this->json('GET', '/api/v1/contacts', [
            'date' => '31-12-2023',   // yyyy-mm-dd ではない
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function test_api_rejects_per_page_out_of_range_min()
    {
        // per_page は min:1 なので、0を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'per_page' => 0,   // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function test_api_rejects_per_page_out_of_range_man()
    {
        // per_page は max:100 なので、101を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'per_page' => 101,   // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /**
     * POST系テスト
     */
    /** @test */
    public function test_api_正常系POSTで成功を返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,  // in:1,2,3 の範囲内
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(201);  // 作成成功は 201
    }

    /** @test */
    public function test_api_異常系POSTで空のfirst_nameのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => '',  // 必須項目を空にしてバリデーションエラーを誘発
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'first_name',
            ]);
    }
    /** @test */
    public function test_api_異常系POSTで長すぎるfirst_nameのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => str_repeat('A', 256),  // 必須項目を256文字にしてバリデーションエラーを誘発
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'first_name',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで空のlast_nameのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => '',  // 必須項目を空にしてバリデーションエラーを誘発
            'first_name' => 'ユーザー',
            'email' => 'user@email.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'last_name',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで長すぎるlast_nameのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => str_repeat('A', 256),  // 必須項目を256文字にしてバリデーションエラーを誘発
            'first_name' => 'ユーザー',
            'email' => 'user@email.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'last_name',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで不正なemailのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'invalid-email',  // 不正なメールアドレス
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで空のemailのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => '',  // 不正なメールアドレス
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'email',
            ]);
    }
    /** @test */
    public function test_api_異常系POSTで長すぎるemailのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => str_repeat('a', 128) . '@' . str_repeat('b', 128) . '.net',  // 不正なメールアドレス
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストコメント',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTcategory_idのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => 99999,  // 存在しない category_id
            'gender' => 1,
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'category_id', 
            ]);
    }

    /** @test */
    public function test_api_異常系POSTでgenderのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 99,  // in:1,2,3 の範囲外
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'gender',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTでtelのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => 'invalid-tel',  // 不正な電話番号
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'tel',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで長すぎるtelのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '012345678901',  // 不正な電話番号
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'tel',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで空のaddressのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '09012345678',
            'address' => '',  // 必須項目を空にしてバリデーションエラーを誘発
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'address',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで長すぎるaddressのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '09012345678',
            'address' => str_repeat('A', 256),  // 必須項目を256文字にしてバリデーションエラーを誘発
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'address',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで空のbuildingのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '01234567890',
            'address' => '東京都',
            'building' => '',  // buildingはnullableなので空にしてもバリデーションエラーを誘発しないはず
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(201);  // バリデーションエラーは出ず 201OK
    }

    /** @test */
    public function test_api_異常系POSTで長すぎるbuildingのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '09012345678',
            'address' => '東京都',  
            'building' => str_repeat('A', 256), // 256文字にしてバリデーションエラーを誘発
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => 'テストメッセージ',
        ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'building',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで空のdetailのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => '',  // 必須項目を空にしてバリデーションエラーを誘発
    ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'detail',
            ]);
    }

    /** @test */
    public function test_api_異常系POSTで長すぎるdetailのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/contacts', [
            'last_name' => 'テスト',
            'first_name' => 'ユーザー',
            'email' => 'user@example.com',
            'category_id' => $this->categoryId,  // 存在する category_id を使用
            'gender' => 1,
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'tag_ids' => [$this->tagId],  // 存在する tag_id を使用
            'detail' => str_repeat('A', 256),  // 必須項目を256文字にしてバリデーションエラーを誘発
    ]);

        $response
            ->assertStatus(422)  // バリデーションエラーは 422
            ->assertJsonValidationErrors([
                'detail',
            ]);
    }
}
