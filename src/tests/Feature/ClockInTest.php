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
        $response->assertSee('勤務中');
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

    //出勤時刻が管理画面で確認できる、内容確認中一旦保留
    public function test_clock_in_time_attendance_list()
    {
        $this->actingAs($this->user)->get('/attendance');
        $this->post(route('attendance.clockIn'));

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->where('date', Carbon::today()->format('Y-m-d'))
                        ->first();

        $clockIn = Carbon::parse($attendance->clock_in)->format('H:i');

        $response = $this->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee($clockIn);
    }
}
