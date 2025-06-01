<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => null,
        ]);
    }

    //会員登録後、認証メールが送信される
    public function test_registration_sent_verification_email()
    {
        Event::fake();
        Notification::fake(); //実際の送信はしない

        $data = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect();
        Event::assertDispatched(Registered::class); //イベント発火の確認
        $this->assertNotNull(session('unauthenticated_user')); //セッションに一時的に保存

        $this->post('/email/verification-notification');
        Notification::assertSentTo(session('unauthenticated_user'), VerifyEmail::class);
    }

    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_verify_button_redirects()
    {
        $response = $this->actingAs($this->user)->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSeeText('認証はこちらから');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        $response = $this->actingAs($this->user)->get($verificationUrl);
    }

    //メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
    public function test_email_verified_redirects()
    {
        //メール認証用のURL生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );

        $this->withSession(['unauthenticated_user' => $this->user]) //セッションに未認証ユーザーのセット
             ->get($verificationUrl) //認証URLへアクセス
             ->assertRedirect('/attendance'); //勤怠登録画面へリダイレクト

        $this->assertTrue($this->user->fresh()->hasVerifiedEmail()); //認証済みになっていること確認
    }
}
