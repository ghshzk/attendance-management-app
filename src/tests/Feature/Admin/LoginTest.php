<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin1@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin'
        ]);
    }

    //メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_error_empty_email()
    {
        $response = $this->post('/admin/login',[
            'email' => '',
            'password' => 'adminpass'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    //パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_error_empty_password()
    {
        $response = $this->post('/admin/login',[
            'email' => 'admin1@example.com',
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
        $response = $this->post('/admin/login',[
            'email' => 'admin2@example.com',
            'password' => 'adminpass'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
