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


class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);
        $this->user = User::where('email', 'user1@example.com')->first();
        $this->status = AttendanceStatus::where('status', '退勤済')->first();
    }

    //自分が行った勤怠情報が全て表示されている
    public function test_list_get_attendances()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => '2025-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::factory()->forAttendance($attendance)->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('05/01(木)');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    //勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_list_current_month_default()
    {
        $currentMonth = Carbon::now()->format('Y/m');

        $response = $this->actingAs($this->user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee($currentMonth);
    }

    //前月を押下した時に表示月の前月の情報が表示される
    public function test_list_prev_month()
    {
        $prevMonth = Carbon::now()->subMonth()->format('Y/m');

        $response = $this->actingAs($this->user)->get(route('attendance.index',[
            'month' => Carbon::now()->subMonth()->format('Y-m-01')]));

        $response->assertStatus(200);
        $response->assertSee($prevMonth);
    }

    //翌月を押下した時に表示月の前月の情報が表示される
    public function test_list_next_month()
    {
        $nextMonth = Carbon::now()->addMonth()->format('Y/m');

        $response = $this->actingAs($this->user)->get(route('attendance.index', [
            'month' => Carbon::now()->addMonth()->format('Y-m-01')
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth);
    }

    //詳細を押下すると、その日の勤怠詳細画面に遷移する
    public function test_list_click_detail()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => '2025-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::factory()->forAttendance($attendance)->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.show',['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('2025年');
        $response->assertSee('5月1日');
    }
}
