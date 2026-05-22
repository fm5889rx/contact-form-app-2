<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * タグ名が空の場合のバリデーションテスト
     */
    public function test_tag_validation(): void
    {
        $user = User::factory()->create();          // ダミーのユーザーを作成

        $response = $this->actingAs($user)
            ->post('/admin/tags', [
            'name' => '',                           // 空のタグ名
        ]);

        $response->assertSessionHasErrors('name');  // バリデーションエラーが発生することを確認
    }

    /**
     * タグの重複バリデーションテスト
     */
    public function test_tag_duplicate_validation(): void
    {
        /**
         * 既に存在するタグを作成してから、同じ名前のタグを作成しようとするテスト
         */
        $user = User::factory()->create();              // ダミーのユーザーを作成

        Tag::create([                        // タグを作成
            'name' => 'Duplicate Tag',
        ]);

        $response = $this->actingAs($user)              // 既に存在するタグ名でタグを作成しようとする
            ->post('/admin/tags', ['name' => 'Duplicate Tag']);

        $response->assertSessionHasErrors('name');      // バリデーションエラーが発生することを確認
    }

    /**
     * タグの最大文字数バリデーションテスト
     */
    public function test_tag_max_length_validation(): void
    {
        /**
         * タグ名が255文字を超える場合のバリデーションテスト
         */
        $longName = str_repeat('a', 256);               // 256文字のタグ名

        $user = User::factory()->create();              // ダミーのユーザーを作成

        $response = $this->actingAs($user)
            ->post('/admin/tags', ['name' => $longName]);     // 長すぎるタグ名でタグを作成しようとする

        $response->assertSessionHasErrors('name');  // バリデーションエラーが発生することを確認
    }
}
