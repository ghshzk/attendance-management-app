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

        $this->admin = User::where('role', 'admin')->first();
        $this->users = User::where('role', 'user')->get();
        $this->user = $this->users->first();
        $this->status = AttendanceStatus::where('status', '退勤済')->first();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $this->status->id,
            'date' => '2025-06-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->break = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    //勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_get_user_attendance_detail()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSeeText('2025年');
        $response->assertSeeText('6月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_get_user_attendance_detail_error_clock_in_after_clock_out()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->admin)->put(route('admin.attendance.update',['id' => $this->attendance->id]), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'remark' => 'テスト修正',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_get_user_attendance_detail_error_break_start_after_clock_out()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->admin)->put(route('admin.attendance.update',['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'remark' => 'テスト修正',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_get_user_attendance_detail_error_break_end_after_clock_out()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->admin)->put(route('admin.attendance.update',['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['17:00'],
            'break_end' => ['18:30'],
            'remark' => 'テスト修正',
        ]);

        $postResponse->assertStatus(302);
        //休憩開始・終了どちらが勤務時間外でも、バリデーションメッセージは開始時間の方にまとめて表示する仕様
        //デザイン上、エラーメッセージを１箇所に集約するため
        $postResponse->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です',
        ]);
    }

    //備考欄が未入力の場合のエラーメッセージが表示される
    public function test_get_user_attendance_detail_error_remark_empty()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->admin)->put(route('admin.attendance.update',['id' => $this->attendance->id]), [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['12:30'],
            'break_end' => ['13:30'],
            'remark' => '',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }
}
