<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceStatus;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;

class AttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $this->user = User::where('email', 'user1@example.com')->first();
        $this->status = AttendanceStatus::where('status', '退勤済')->first();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => '2025-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->break =BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    //勤怠詳細画面の名前がログインユーザーの氏名になっている
    public function test_detail_show_user_name()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    //勤怠詳細画面の日付が選択した日付になっている
    public function test_detail_show_select_date()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show',['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('2025年');
        $response->assertSee('5月1日');
    }

    //出勤・退勤にて記されている時間がログインユーザーの打刻と一致している
    public function test_detail_show_user_stamp_time()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    //休憩にて記されている時間がログインユーザーの打刻と一致している
    public function test_detail_show_user_stamp_break_time()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}