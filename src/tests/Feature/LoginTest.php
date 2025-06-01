<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テスト',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
    }

    //メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_error_empty_email()
    {
        $response = $this->post('/login',[
            'email' => '',
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    //パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_error_empty_password()
    {
        $response = $this->post('/login',[
            'email' => 'test1@example.com',
            'password' => ''
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    //登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_error_login_credentials_mismatch()
    {
        $response = $this->post('/login',[
            'email' => 'test2@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
