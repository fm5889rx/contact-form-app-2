<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;   // データベースをリフレッシュするトレイト
    
    /**
     * カテゴリーテーブルのテスト
     */
    public function test_category_has_many(): void
    {
        // カテゴリーのダミーデータを作成
        $category = Category::create([
            'content' => 'Test Category',
        ]);

        // カテゴリーに関連するお問い合わせが存在しないことを確認
        $this->assertEmpty($category->contacts, 'カテゴリーに関連するお問い合わせは存在しません。');

        // カテゴリーに関連するお問い合わせを作成
        $category->contacts()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'user@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'detail' => 'お問い合わせ詳細',
        ]);
        $contact = $category->contacts()->first();  // カテゴリーに関連する最初のお問い合わせを取得

        /*
         * カテゴリーに関連するお問い合わせが存在することを確認
         */
        $this->assertNotEmpty($contact, 'カテゴリーに関連するお問い合わせが存在します。');
        
        /*
         * カテゴリーに関連するお問い合わせの内容を確認
         */
        $this->assertEquals('太郎', $contact->first_name, 'お問い合わせの名が正しいことを確認します。');
        $this->assertEquals('山田', $contact->last_name, 'お問い合わせの姓が正しいことを確認します。');
        $this->assertEquals(1, $contact->gender, 'お問い合わせの性別が正しいことを確認します。');
        $this->assertEquals('user@example.com', $contact->email, 'お問い合わせのメールアドレスが正しいことを確認します。');
        $this->assertEquals('09012345678', $contact->tel, 'お問い合わせの電話番号が正しいことを確認します。');
        $this->assertEquals('東京都', $contact->address, 'お問い合わせの住所が正しいことを確認します。');
        $this->assertEquals('お問い合わせ詳細', $contact->detail, 'お問い合わせの詳細が正しいことを確認します。');
    }

    /*
     * カテゴリーの名前が空の場合のバリデーションテスト
     */
    public function test_category_name_validation(): void
    {
        $category = new Category([
            'content' => '',                // 空のカテゴリー名
        ]);

        // カテゴリーの属性とバリデーションルールを使用してバリデーションデータを作成
        $validator = \Validator::make($category->toArray(), Category::$rules);

        // バリデーションエラーが発生することを確認
        $this->assertTrue($validator->fails(), 'カテゴリーの名前が空の場合はバリデーションエラーが発生します。');
    }

    /*
     * カテゴリーの名前が255文字を超える場合のバリデーションテスト
     */
    public function test_category_name_max_length_validation(): void
    {
        $longName = str_repeat('a', 256);   // 256文字のカテゴリー名
        $category = new Category([
            'content' => $longName,         // 長すぎるカテゴリー名
        ]);

        // カテゴリーの属性とバリデーションルールを使用してバリデーションデータを作成
        $validator = \Validator::make($category->toArray(), Category::$rules);

        // バリデーションエラーが発生することを確認
        $this->assertTrue($validator->fails(), 'カテゴリーの名前が255文字を超える場合はバリデーションエラーが発生します。');
    }

    /*
     * カテゴリーの名前が255文字以下の場合のバリデーションテスト
     */
    public function test_category_name_valid_length_validation(): void
    {
        $validName = str_repeat('a', 255);  // 255文字のカテゴリー名
        $category = new Category([
            'content' => $validName,        // 有効なカテゴリー名
        ]);

        // カテゴリーの属性とバリデーションルールを使用してバリデーションデータを作成
        $validator = \Validator::make($category->toArray(), Category::$rules);

        // バリデーションエラーが発生しないことを確認
        $this->assertFalse($validator->fails(), 'カテゴリーの名前が255文字以下の場合はバリデーションエラーが発生しません。');
    }

    /*
     * カテゴリーの名前が重複する場合のバリデーションテスト
     */
    public function test_category_name_duplicate_validation(): void
    {
        // 最初のカテゴリーを作成
        Category::create([
            'content' => 'Duplicate Category',
        ]);

        // 同じ名前のカテゴリーを作成しようとする
        $category = new Category([
            'content' => 'Duplicate Category',      // 重複するカテゴリー名
        ]);

        // カテゴリーの属性とバリデーションルールを使用してバリデーションデータを作成
        $validator = \Validator::make($category->toArray(), Category::$rules);

        // バリデーションエラーが発生することを確認
        $this->assertTrue($validator->fails(), 'カテゴリーの名前が重複する場合はバリデーションエラーが発生します。');
    }

    /*
     * カテゴリーの名前が空でない場合のバリデーションテスト
     */
    public function test_category_name_not_empty_validation(): void
    {
        // 有効な名前のカテゴリーを作成しようとする
        $category = new Category([
            'content' => 'Unique Category',         // ユニークなカテゴリー名
        ]);

        // カテゴリーの属性とバリデーションルールを使用してバリデーションデータを作成
        $validator = \Validator::make($category->toArray(), Category::$rules);

        // バリデーションエラーが発生しないことを確認
        $this->assertFalse($validator->fails(), 'カテゴリーの名前が空でない場合はバリデーションエラーが発生しません。');
    }
}
