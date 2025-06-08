<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceStatus;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CorrectionRequestTest extends TestCase
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
            'date' => '2025-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->break = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $this->pendingCorrection = CorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'remark' => 'テスト承認待ち',
            'status' => 'pending'
        ]);

        $this->correctionBreakTime = CorrectionBreakTime::factory()->create([
            'correction_request_id' => $this->pendingCorrection->id,
            'break_start' => '12:30:00',
            'break_end' => '13:30:00',
        ]);

        $this->approvedCorrection = CorrectionRequest::factory()->approved()->create([
            'attendance_id' => $this->attendance->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:30:00',
            'remark' => 'テスト承認済み',
            'status' => 'approved'
        ]);
    }

    //承認待ちの修正申請が全て表示されている
    public function test_list_get_all_pending_correction_request()
    {
        $response = $this->actingAs($this->admin)->get(route('correction.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSeeText('テスト承認待ち');
        $response->assertDontSeeText('テスト承認済み');
    }

    //承認済みの修正申請が全て表示されている
    public function test_list_get_all_approved_correction_request()
    {
        $response = $this->actingAs($this->admin)->get(route('correction.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSeeText('テスト承認済み');
        $response->assertDontSeeText('テスト承認待ち');
    }

    //修正申請の詳細内容が正しく表示されている
    public function test_get_correction_request_detail()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.approve.show', $this->pendingCorrection->id));

        $response->assertStatus(200);
        $response->assertSeeText('2025年');
        $response->assertSeeText('5月1日');
        $response->assertSeeText('09:30');
        $response->assertSeeText('18:30');
        $response->assertSeeText('12:30');
        $response->assertSeeText('13:30');
        $response->assertSeeText('テスト承認待ち');
    }

    //修正申請の承認処理が正しく行われる
    public function test_approve_correction_request_detail()
    {
        $response = $this->actingAs($this->admin)->put(route('admin.approve.update', $this->pendingCorrection->id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('correction_requests', [
            'id' => $this->pendingCorrection->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:30:00',
            'break_end' => '13:30:00',
        ]);
    }
}
