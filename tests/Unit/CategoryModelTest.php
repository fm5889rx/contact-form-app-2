<?php

namespace Tests\Unit;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;   // データベースをリフレッシュするトレイト
    
    /**
     * カテゴリーテーブルのテスト
     */
    public function test_category_has_many(): void
    {
        // カテゴリーのダミーデータを作成
        $category = Category::factory()->create();

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
}
