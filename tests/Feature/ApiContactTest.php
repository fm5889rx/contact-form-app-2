<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use RefreshDatabase;                        // データベースをリフレッシュするトレイト

    /** @var int */
    protected $categoryId;                          // テスト用に保持するcategory_id
    protected $contactId;                           // テスト用に保持するcontact_id
    protected $tagId;                               // テスト用に保持するtag_ids

    public function setUp(): void
    {
        parent::setUp();

        // テスト用のcategoriesデータを作成
        $category = Category::create([              // category_id が存在することを保証
            'id' => 1,
            'content' => 'テストカテゴリ'
        ]);
        $this->categoryId = $category->id;          // 作成されたcategory_idを保持 

        // テスト用のtagsデータを作成
        $tag = Tag::create([                        // tag_idsが存在することを保証
            'name' => 'テストタグ',
        ]);
        $this->tagId = $tag->id;                    // 作成されたtag_idを保持

        // Contact モデルのファクトリーを使用してテストデータを作成
        $contact = Contact::factory()->create([
            'category_id' => $category->id,         // 存在する category_id を使用
        ]);
        $this->contactId = $contact->id;            // 作成されたcontact_idを保持

        // テスト用のピボットテーブルを作成
        $contact->tags()->attach($this->tagId);     // 作成されたcontact_idとtag_idを紐つけ
    }

    /**
     * APIエンドポイントのテスト.
     */
    /**
     * お問合せ一覧・検索
     */
    public function test_api_お問い合わせ一覧をJSON型式で取得できる(): void
    {
        $response = $this->json('GET', '/api/v1/contacts');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_api_お問合せを検索できる_正常系()
    {
        // API に対して有効なクエリパラメータを送信
        $response = $this->getJson('/api/v1/contacts', [
            'keyword'      => '',                   // 検索対象では無い
            'gender'       => 1,                    // 存在する gender を使用
            'category_id'  => $this->categoryId,    // 存在する category_id を使用
            'date'         => '',                   // 検索対象ではない
            'per_page'     => 7,                    // デフォルトのページネーション
            'page'         => 1,                    // 戦闘ページを指定
        ]);

        $response->assertSuccessful();              // 200 系レスポンスを期待

        $response->assertJsonStructure([            // 必要に応じて返却データ構造を確認
            'data',
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function test_api_genderに数値以外の異常値を入れて検索()
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
    public function test_api_genderに範囲外の数値を入れて検索()
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
    public function test_api_存在しないカテゴリーIDを入れて検索()
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
    public function test_api_不正な日付フォーマットで検索()
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
    public function test_api_範囲外のper_pageで検索（最小）()
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
    public function test_api_範囲外のper_pageで検索（最大）()
    {
        // per_page は max:100 なので、101を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'per_page' => 101,   // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function test_api_範囲外のpageで検索（最小）()
    {
        // per_page は min:1 なので、0を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/contacts', [
            'page' => 0,   // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['page']);
    }

    /**
     * お問合せ詳細
     */
    /** @test */
    public function test_api_指定のIDでお問合せ詳細を取得()
    {
        $uri = sprintf('/api/v1/contacts/%d', $this->contactId); // URIを生成

        $response = $this->json('GET', $uri);       // 指定IDでGETメソッドを実行

        $response->assertSuccessful();              // 200 系レスポンスを期待
    }

    /** @test */
    public function test_api_存在しないIDでお問合せ詳細を取得()
    {
        $uri = sprintf('/api/v1/contacts/%d', 99999); // URIを生成

        $response = $this->json('GET', $uri);       // 指定IDでGETメソッドを実行

        $response->assertStatus(404);               // 404 エラーレスポンスを期待
    }

    /**
     * お問い合わせ作成
     */
    /** @test */
    public function test_api_お問い合わせ作成_正常系()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 正常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(201)                // 201 レスポンスを期待
            ->assertJsonStructure([                 // 返却JSONが正しい構造を持つか確認
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'gender',
//                    'category_id',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
//                    'tag_ids',
                ]
            ]);

        $this->assertDatabaseHas('contacts', [  // DBにレコードが作成されているか確認
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => NULL,
            'detail' => 'test',
        ]);

        $this->assertDatabaseHas('contact_tag', [  // 多対多のピボットテーブルを確認
            'contact_id' => $this->contactId,
            'tag_id' => $this->tagId,
        ]);
    }

    /** @test */
    public function test_api_お問い合わせ作成_first_nameが空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => '',                     // first_nameが空
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
                 ->assertJsonValidationErrors(['first_name']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_first_nameが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => str_repeat('A', 256),   // first_nameが256文字
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['first_name']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_last_nameが空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => '',                      // last_nameが空
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['last_name']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_last_nameが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => str_repeat('A', 256),    // last_nameが256文字
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['last_name']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_genderが空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => '',                         // genderが空
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['gender']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_genderが範囲外()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 9,                          // in:1,2,3以外
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['gender']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_caterory_idが範囲外()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => 99999,                 // 不正なcategory_id
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['category_id']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_不正なメールアドレス()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'userexample.com',           // メール形式でない
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['email']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_メールアドレスが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => str_repeat('A', 128) . '@' . str_repeat('A', 128) . '.net',                                   // メールアドレスが256文字以上
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['email']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_電話番号が空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '',                            // telが空
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['tel']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_電話番号が短い()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '012345678',                   // telが10文字未満
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['tel']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_電話番号が長い()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '012345678901',                // telが12文字以上
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['tel']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_addressが空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => '',                        // addressが空
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['address']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_addressが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => str_repeat('A', 256),      // addressが256文字
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['address']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_buildingが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => str_repeat('A', 256),     // buildingが256文字
            'detail' => 'test',
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)                // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['building']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_detailが空()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => '',                         // detailが空
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['detail']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_detailが長すぎる()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => str_repeat('A', 121),       // detailが121文字
            'tag_ids' => [$this->tagId],
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['detail']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_tag_idsが配列でない()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => $this->tagId,              // tag_idsが配列でない
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['tag_ids']);  // エラー内容を確認
    }

    /** @test */
    public function test_api_お問い合わせ作成_tag_idsが不正()
    {
        $uri = '/api/v1/contacts';                  // URIを作成

        $response = $this->json('POST', $uri, [     // 異常データでPOST
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'email' => 'user@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => '',                       // nullableなので省略
            'detail' => 'test',
            'tag_ids' => 99999,                     // 不正なtag_ids
        ]);

        $response->assertStatus(422)               // 422 エラーレスポンスを期待
            ->assertJsonValidationErrors(['tag_ids']);  // エラー内容を確認
    }

    /**
     * お問い合わせ更新
     */
    /** @test */
    public function test_api_お問い合わせ更新_正常系()
    {
        $uri = sprintf('/api/v1/contacts/%d', $this->contactId); // URIを生成

        $response = $this->json('PUT', $uri, [      // 指定IDでGETメソッドを実行
            'first_name' => 'NewName',
            'email' => 'new@example.com',
        ]);

        $response->assertSuccessful();              // 200 系レスポンスを期待

        $this->assertDatabaseHas('contacts', [      // DBが更新されたか確認
            'id' => $this->contactId,
            'first_name' => 'NewName',
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function test_api_存在しないIDでお問い合わせを更新()
    {
        $uri = sprintf('/api/v1/contacts/%d', 99999); // URIを生成

        $response = $this->json('PUT', $uri);       // 指定IDでGETメソッドを実行

        $response->assertStatus(404);               // 404 エラーレスポンスを期待
    }

    /**
     * お問い合わせ削除
     */
    /** @test */
    public function test_api_お問い合わせ削除_正常系()
    {
        $uri = sprintf('/api/v1/contacts/%d', $this->contactId); // URIを生成

        $response = $this->json('DELETE', $uri);    // 指定IDでGETメソッドを実行

        $response->assertSuccessful();              // 200 系レスポンスを期待

        $this->assertDatabaseMissing('contacts', [  // DBから削除されているか確認
            'id' => $this->contactId,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $this->contactId,
        ]);
    }

    /** @test */
    public function test_api_存在しないIDでお問い合わせを削除()
    {
        $uri = sprintf('/api/v1/contacts/%d', 99999); // URIを生成

        $response = $this->json('DELETE', $uri);       // 指定IDでGETメソッドを実行

        $response->assertStatus(404);               // 404 エラーレスポンスを期待
    }
}

