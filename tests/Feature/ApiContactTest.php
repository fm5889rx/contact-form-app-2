<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    /**
     * エンドポイントのテスト.
     */
    public function test_お問い合わせ一覧をJSON型式で取得できる(): void
    {
        $response = $this->get('/api/v1/contacts');

        $response->assertStatus(200);
    }
}
