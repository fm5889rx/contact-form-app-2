<?php

namespace Tests\Feature;

use App\Http\Requests\ApiIndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;    // データベースをリフレッシュするトレイト

    private $validData;     // テストデータを格納する変数
    private $rules;         // バリデーションルールを格納する変数
    private $categoryId;    // カテゴリーIDを保持しておく変数

    /**
     * テスト前の共通処理
     * 正常値を保存
     * 各テストでは異常値だけ書き換えるようにする
     */
    protected function setUp(): void
    {
        parent::setUp();

        // カテゴリーのダミーデータを作成
        $category = Category::create([
            'content' => 'カテゴリー1',
        ]);
        $this->categoryId = $category->id;  // カテゴリーIDを保持

        // 正常値の準備
        $this->validData = [
            'first_name' => 'Taro',
            'last_name' => 'Tanaka',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '01234567890',
            'address' => 'Tokyo',
            'building' => 'Building',
            'category_id' => $category->id,
            'detail' => 'Detail',
        ];
    }

    /**
     * 管理者ページの表示テスト
     */
    public function test_admin_page_display(): void
    {
        Contact::factory()->count(20)->create([     // 20件のテストデータを作成
            'category_id' => $this->categoryId,
        ]);

        $admin = User::factory()->create();         // 管理者ユーザーを作成

        $this->actingAs($admin);                    // 管理者ユーザーで認証

        $response = $this->get('/admin');       // 管理者ページのURLにGETリクエストを送信

        $response->assertStatus(200);               // HTTPステータスコード200を期待
    }

    /**
     * お問い合わせ検索
     */
    /** 正常値 **/
    public function test_all_required_search_field_pass()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'keyword' => '',
            'gender' => 1,
            'category_id' => $this->categoryId,
            'date' => '',
            'per_page' => 7,
            'page' => 1,
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->passes(), '判定値は正常です。');
    }

    /** keywordが不正 **/
    public function test_invalid_search_value_keyword()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'keyword' => str_repeat('A', 256),  // keywordが256文字
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'keywordは255文字以下です。');
    }

    /** genderが範囲外 **/
    public function test_invalid_search_value_keyword_too_long()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'gender' => 4,  // genderはin:1,2,3
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'genderは1,2,3のいずれかです。');
    }

    /** keywordが不正 **/
    public function test_invalid_search_value_category_id()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'category_id' => 99999,  // 不正なcategory_id
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'category_idが不正です。');
    }

    /** 日付が不正 **/
    public function test_invalid_search_value_date()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'date' => '31-12-2026',  // 日付書式はY-m-d
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '日付書式はY-m-dです。');
    }

    /** per_pageが不正(最小) **/
    public function test_invalid_search_value_per_page_min()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'per_page' => 0,  // per_pageはmin:1
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'per_pageは1〜100の範囲です。');
    }


    /** per_pageが不正(最大) **/
    public function test_invalid_search_value_per_page_max()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'per_page' => 101,  // per_pageはmax:100
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'per_pageは1〜100の範囲です。');
    }

    /** pageが不正 **/
    public function test_invalid_search_value_page()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();
        // 正常値の準備
        $this->validData = [
            'page' => 0,  // pageはmin:1
        ];

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'pageは1以上です。');
    }

    /**
     * お問い合わせ詳細表示のテスト
     */
    public function test_admin_contact_data_show()
    {
        // バリデーションルールを取得する
        $this->rules = (new ApiIndexContactRequest())->rules();

        // Contactデータの準備
        $contact = Contact::factory()->create([
            'category_id' => $this->categoryId,
        ]);

        // 正常値の準備（ページ関係だけ）
        $this->validData = [
            'per_page' => 7,
            'page'     => 1,
        ];

        $admin = User::factory()->create();             // 管理者ユーザ情報を作成
        $this->actingAs($admin);                        // 管理者ユーザでログイン

        // 詳細取得のためのGETリクエストを送る
        $response = $this->get("/admin/contacts/{$contact->id}");

        // 判定
        $response->assertStatus(200)                    // レコード入手できたことを期待
                ->assertViewIs('admin.show')            // 画面推移できたことを期待
                ->assertViewHas('contact', $contact);   // Bladeにデータが渡っている
    }
    /**
     * お問い合わせ削除のテスト
     */
    public function test_admin_contact_data_deleted()
    {
        $contact = Contact::factory()->create([         // 操作対象のレコードを作成
            'category_id' => $this->categoryId,
        ]);

        $admin = User::factory()->create();             // 管理者ユーザ情報を作成
        $this->actingAs($admin);                        // 管理者ユーザでログイン

        $this->get("/admin/contacts/{$contact->id}")
            ->assertStatus(200);                    // viewなので200OKが帰ることを期待

        $response = $this->delete("/admin/contacts/{$contact->id}");  // 削除を実行

        $response->assertRedirect('/admin');            // 一覧画面に戻ることを期待

        // テーブルからレコードが消えていることを確認
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}