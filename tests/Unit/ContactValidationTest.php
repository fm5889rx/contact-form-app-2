<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    private $validData;     // テストデータを格納する変数
    private $rules;         // バリデーションルールを格納する変数

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

        // 正常値の準備
        $this->validData = [
            'category_id' => $category->id,
            'first_name'  => '太郎',
            'last_name'   => '山田',
            'gender'      => 1,
            'email'       => 'taro@example.com',
            'tel'         => '09012345678',
            'address'     => '東京都',
            'detail'      => 'お打ち合わせ詳細',
        ];

        // バリデーションルールを取得する
        $this->rules = (new StoreContactRequest())->rules();
    }

    /**
     * 正常値の判定
     */
    public function test_all_required_field_pass(): void
    {
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
    public function test_invalid_first_name(): void
    {
        // 判定値の準備
        $this->validData['first_name'] = '';        // first_nameがNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'first_nameは必須です。');
    }

    /**
     * last_nameの異常値判定
     */
    public function test_invalid_last_name(): void
    {
        // 判定値の準備
        $this->validData['last_name'] = '';        // last_nameがNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'last_nameは必須です。');
    }

    /**
     * 性別値の異常値判定
     */
    public function test_invalid_gender_value(): void
    {
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
    public function test_invalid_mail_address(): void
    {
        // 判定値の準備
        $this->validData['email'] = 'example.com';     // メール形式でない

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'メール形式ではありません。');
    }

    /**
     * 電話番号の異常値判定
     */
    public function test_invalid_phone_number(): void
    {
        // 判定値の準備
        $this->validData['tel'] = '090-1234-5678';   // 正規表現と異なる

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '電話番号の形式ではありません。');
    }

    /**
     * 住所の異常値判定
     */
    public function test_invalid_address_value(): void
    {
        // 判定値の準備
        $this->validData['address'] = '';        // 住所がNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), '住所は必須です。');
    }

    /**
     * お問い合わせ詳細の異常値判定
     */
    public function test_invalid_detail_value(): void
    {
        // 判定値の準備
        $this->validData['detail'] = '';       // お問い合わせ詳細がNULL

        // バリデーションチェック
        $validator = Validator::make($this->validData, $this->rules);

        // 判定
        $this->assertTrue($validator->fails(), 'お問い合わせ詳細は必須です。');
    }

}
