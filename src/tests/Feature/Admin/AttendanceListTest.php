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

class AttendanceListTest extends TestCase
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
        $this->status = AttendanceStatus::where('status', '退勤済')->first();

        $this->today = Carbon::today()->format('Y-m-d');
        $this->yesterday = Carbon::yesterday()->format('Y-m-d');
        $this->tomorrow = Carbon::tomorrow()->format('Y-m-d');

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $this->status->id,
                'date' => $this->today,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);

            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $this->status->id,
                'date' => $this->yesterday,
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
            ]);

            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $this->status->id,
                'date' => $this->tomorrow,
                'clock_in' => '09:30:00',
                'clock_out' => '18:30:00',
            ]);
        }
    }

    //その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_list_get_all_users_attendance_today()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText('鈴木 二郎');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');

        $response->assertDontSeeText('09:30');
        $response->assertDontSeeText('19:00');
    }

    //遷移した際に現在の日付が表示される
    public function test_list_current_date_default()
    {
        $currentDate = Carbon::now()->format('Y/m/d');

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSeeText($currentDate);
    }

    //「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_list_prev_date()
    {
        $prevDate = Carbon::now()->subDay()->format('Y/m/d');

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index',[
            'date' => Carbon::now()->subDay()->format('Y-m-d')]));

        $response->assertStatus(200);

        $response->assertSeeText('10:00');
        $response->assertSeeText('19:00');

        $response->assertDontSeeText('09:00');
        $response->assertDontSeeText('18:30');
    }

    //「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_list_next_date()
    {
        $nextDate = Carbon::now()->addDay()->format('Y/m/d');

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index',[
            'date' => Carbon::now()->addDay()->format('Y-m-d')]));

        $response->assertStatus(200);

        $response->assertSeeText('09:30');
        $response->assertSeeText('18:30');

        $response->assertDontSeeText('10:00');
        $response->assertDontSeeText('18:00');
    }
}
