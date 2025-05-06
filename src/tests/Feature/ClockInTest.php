<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;


class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $statuses;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $this->statuses = AttendanceStatus::pluck('id', 'status');
        $this->user = User::where('email', 'user1@example.com')->first();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //出勤ボタンが正しく機能する
    public function test_clock_in_button_works()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('出勤'); //出勤ボタンが表示されていることの確認

        $this->post(route('attendance.clockIn')); //出勤ボタンが押した時の処理

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    //出勤は1日1回のみできる
    public function test_clock_in_only_once()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
            'attendance_status_id' => $this->statuses['退勤済'],
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    //出勤時刻が管理画面で確認できる、一旦保留
}
