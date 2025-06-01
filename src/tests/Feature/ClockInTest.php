<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;


class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $statuses;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now()); //テスト中の時刻を固定

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
        $response->assertSeeText('出勤'); //出勤ボタンが表示されていることの確認

        $response = $this->post(route('attendance.clockIn')); //出勤ボタンが押した時の処理
        $response->assertRedirect(route('attendance.create'));

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('勤務中');
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
        $response->assertDontSeeText('出勤');
    }

    //出勤時刻が管理画面(勤怠一覧画面)で確認できる
    public function test_clock_in_time_attendance_list()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $this->post(route('attendance.clockIn'));

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->where('date', Carbon::today()->format('Y-m-d'))
                        ->first();
        $this->assertNotNull($attendance);

        $clockIn = Carbon::parse($attendance->clock_in)->format('H:i');

        $response = $this->actingAs($this->user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSeeText($clockIn);
    }
}
