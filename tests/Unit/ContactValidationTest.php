<?php

namespace Tests\Unit;

use App\Http\Requests\ApiIndexContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    private $validData;     // テストデータを格納する変数
    private $rules;         // バリデーションルールを格納する変数
    private $categoryId;    // カテゴリーIDを保持しておく変数

    use RefreshDatabase;   // データベースをリフレッシュするトレイト
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
     * 正常値の判定
     */
    public function test_all_required_field_pass(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->passes(), '判定値は正常です。');
    }

    /**
     * カテゴリーIDの異常値判定
     */
    public function test_invalid_category_id(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['category_id'] = 6;   // カテゴリーIDは1〜5

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'カテゴリーIDは異常です。');
    }

    /**
     * first_nameの異常値判定
     */
    /** first_nameが空の場合 **/
    public function test_invalid_first_name(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['first_name'] = '';        // first_nameがNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'first_nameは必須です。');
    }

    /** first_nameが長すぎる場合 **/
    public function test_invalid_first_name_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['first_name'] = str_repeat('A', 256);  // first_nameが256文字

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'first_nameは255文字以下です。');
    }

    /**
     * last_nameの異常値判定
     */
    /** last_nameが空の場合 */
    public function test_invalid_last_name(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['last_name'] = '';        // last_nameがNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'last_nameは必須です。');
    }

    /** last_nameが長すぎる場合 */
    public function test_invalid_last_name_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['last_name'] = str_repeat('A', 256);  // last_nameが256文字

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'last_nameは255文字以下です。');
    }

    /**
     * 性別値の異常値判定
     */
    public function test_invalid_gender_value(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['gender'] = 4;      // 性別値は1〜3

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '性別値が異常です。');
    }

    /**
     * メールアドレスの異常値判定
     */
    /** 不正なメールアドレスの場合 */
    public function test_invalid_mail_address(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['email'] = 'example.com';     // メール形式でない

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'メール形式ではありません。');
    }

    /** メールアドレスが長すぎる場合 */
    public function test_invalid_mail_address_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['email'] = str_repeat('A', 128) . '@' . str_repeat('B', 128) . 'com';  // メールアドレスが256文字以上

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'メールアドレスは255文字以下です。');
    }

    /**
     * 電話番号の異常値判定
     */
    /** 不正な電話番号 */
    public function test_invalid_phone_number(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['tel'] = '090-1234-5678';      // 正規表現と異なる

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '電話番号の形式ではありません。');
    }

    /** 不正な電話番号 */
    public function test_invalid_phone_number_too_short(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['tel'] = '090123456';          // 電話番号が9桁

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '電話番号は10〜11桁です。');
    }

    /** 不正な電話番号 */
    public function test_invalid_phone_number_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['tel'] = '090123456789';       // 電話番号が12桁

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '電話番号は10〜11桁です。');
    }

    /**
     * 住所の異常値判定
     */
    /** 住所が空の場合 */
    public function test_invalid_address_value(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['address'] = '';        // 住所がNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '住所は必須です。');
    }

    /** 住所が長すぎる場合 */
    public function test_invalid_address_value_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['address'] = str_repeat('A', 256);  // 住所が256文字

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '住所は255文字以下です。');
    }

    /**
     * 建物名の異常値判定
     */
    /** 住所が長すぎる場合 */
    public function test_invalid_building_value_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['building'] = str_repeat('A', 256);  // 建物名が256文字

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '建物名は255文字以下です。');
    }

    /**
     * お問い合わせ詳細の異常値判定
     */
    /** お問い合わせ詳細が空の場合 */
    public function test_invalid_detail_value(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['detail'] = '';       // お問い合わせ詳細がNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'お問い合わせ詳細は必須です。');
    }

    /** お問い合わせ詳細が長すぎる場合 */
    public function test_invalid_detail_value_too_long(): void
    {
        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();

        // 判定値の準備
        $this->validData['detail'] = str_repeat('A', 121);  // お問い合わせ詳細が121文字

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'お問い合わせ詳細は120文字以下です。');
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
}
