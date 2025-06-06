<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceStatus;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now());
        $this->seed([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $this->admin = User::where('role', 'admin')->first();
        $this->users = User::where('role', 'user')->get();
        $this->user = $this->users->first();
        $this->status = AttendanceStatus::where('status', '退勤済')->first();

        $this->today = Carbon::today()->format('Y-m-d');
        $this->prevMonthDay = Carbon::now()->subMonth()->format('Y-m-d');
        $this->nextMonthDay = Carbon::now()->addMonth()->format('Y-m-d');
    }

    //管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_list_get_all_users_name_email()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText('鈴木 二郎');
        $response->assertSeeText('user1@example.com');
        $response->assertSeeText('user2@example.com');
    }

    //ユーザーの勤怠情報が正しく表示される
    public function test_list_get_user_attendance()
    {
        $today = Carbon::today()->format('m/d');

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => $this->today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.attendance', ['id' => $this->user->id]));

        $response->assertStatus(200);
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText($today);
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    //「前月」を押下した時に表示月の前月の情報が表示される
    public function test_list_get_user_attendance_prev_month()
    {
        $prevMonthDay = Carbon::now()->subMonth()->format('m/d');

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => $this->prevMonthDay,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.attendance', [
            'id' => $this->user->id,
            'month' => Carbon::now()->subMonth()->format('Y-m-01')
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText($prevMonthDay);
        $response->assertSeeText('10:00');
        $response->assertSeeText('19:00');
    }

    //「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_list_get_user_attendance_next_month()
    {
        $nextMonthDay = Carbon::now()->addMonth()->format('m/d');

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => $this->nextMonthDay,
            'clock_in' => '08:00:00',
            'clock_out' => '17:30:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.attendance', [
            'id' => $this->user->id,
            'month' => Carbon::now()->addMonth()->format('Y-m-01')
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText($nextMonthDay);
        $response->assertSeeText('08:00');
        $response->assertSeeText('17:30');
    }

    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_list_get_user_attendance_detail()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => '2025-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakTime::factory()->forAttendance($attendance)->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSeeText('勤怠詳細');
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText('2025年');
        $response->assertSeeText('6月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
