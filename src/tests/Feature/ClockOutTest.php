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

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $statuses;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());

        $this->seed([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $this->statuses = AttendanceStatus::pluck('id', 'status');
        $this->user = User::where('email', 'user1@example.com')->first();
    }


    //退勤ボタンが正しく機能する
    public function test_clock_out_button_works()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'attendance_status_id' => $this->statuses['勤務中'],
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSeeText('退勤');

        $response = $this->post(route('attendance.clockOut'));

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }

    //退勤時刻が管理画面(勤怠一覧画面)で確認できる
    public function test_clock_out_time_attendance_list()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $this->post(route('attendance.clockIn'));
        $this->post(route('attendance.clockOut'));

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->where('date', Carbon::today()->format('Y-m-d'))
                        ->first();
        $this->assertNotNull($attendance);

        $clockOut = Carbon::parse($attendance->clock_out)->format('H:i');

        $response = $this->actingAs($this->user)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSeeText($clockOut);
    }
}
