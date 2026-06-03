<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;                        // データベースをリフレッシュするトレイト
    use MakesHttpRequests;                      // HTTP系メソッドを提供するトレイト

    /** @test */
    public function test_api_accepts_valid_query_parameters()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'keyword'      => 'example',
            'gender'       => 1,
            'category_id'  => 5,     // この ID が categories テーブルに存在することを想定
            'date'         => '2023-12-01',
            'per_page'     => 7,
            'page'         => 2,
        ]);

        $response->assertSuccessful();              // 200 系レスポンスを期待

        $response->assertJsonStructure([            // 必要に応じて返却データ構造を確認
            'data',
            'links',
            'meta',
        ]);
    }

    /** @test */
    public function test_api_rejects_invalid_keyword()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'keyword' => '',   // 空の文字列
        ]);

        $response
            ->assertStatus(422)                                     // バリデーション失敗時は 422
            ->assertJsonValidationErrors(['keyword']);               // ‘keyword’ にエラーがあることを保証
    }

    /** @test */
    public function test_api_rejects_invalid_gender_value()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'gender' => 'invalid',   // 文字列・数値以外
        ]);

        $response
            ->assertStatus(422)                                     // バリデーション失敗時は 422
            ->assertJsonValidationErrors(['gender']);               // ‘gender’ にエラーがあることを保証
    }

    /** @test */
    public function test_api_rejects_gender_out_of_range()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'gender' => 99,   // in:1,2,3 の範囲外
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function test_api_rejects_non_existent_category_id()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'category_id' => 99999,   // 存在しない ID
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function test_api_rejects_invalid_date_format()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'date' => '31-12-2023',   // yyyy-mm-dd ではない
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function test_api_rejects_per_page_out_of_range()
    {
        $response = $this->getJson('/api/v1/contacts', [
            'per_page' => 0,   // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }
}