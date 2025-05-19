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
use App\Models\CorrectionRequest;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;


class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    public function setUp(): void
    {
        parent::setup();
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

    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_error_clock_in_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]),[
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
    public function test_error_break_start_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'remark' => 'テスト修正',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'break_start.0' => '休憩時間が勤務時間外です'
        ]);
    }

    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_error_break_end_after_clock_out()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['17:00'],
            'break_end' => ['18:30'],
            'remark' => 'テスト修正',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'break_end.0' => '休憩時間が勤務時間外です',
        ]);
    }

    //備考欄が未入力の場合のエラーメッセージが表示される
    public function test_error_remark_empty()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'remark' => '',
        ]);

        $postResponse->assertStatus(302);
        $postResponse->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }

    //修正申請処理が実行される
    public function test_correction_request_success()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));

        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => ['12:15'],
            'break_end' => ['13:15'],
            'remark' => 'テスト修正申請',
        ]);

        $postResponse->assertStatus(302);
    //管理者ユーザーで承認画面と申請一覧を確認するの部分がまだ画面がないため保留
    }

    //「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    public function test_correction_request_list_pending()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));
        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => ['12:15'],
            'break_end' => ['13:15'],
            'remark' => 'テスト修正申請',
        ]);

        $postResponse->assertStatus(302);

        $listResponse = $this->actingAs($this->user)->get(route('correction.index'));

        $listResponse->assertStatus(200);
        $listResponse->assertSee('テスト修正申請');
    }

    //「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_correction_request_list_approved()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));
        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => ['12:15'],
            'break_end' => ['13:15'],
            'remark' => 'テスト修正申請',
        ]);

        $postResponse->assertStatus(302);
    //管理者承認の画面まだのため一旦保留
    }

    //各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function test_correction_request_list_detail_show()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $this->attendance->id]));
        $postResponse = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $this->attendance->id]), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => ['12:15'],
            'break_end' => ['13:15'],
            'remark' => 'テスト修正申請',
        ]);
        $listResponse = $this->actingAs($this->user)->get(route('correction.index'));

        $correctionRequest = CorrectionRequest::where('attendance_id', $this->attendance->id)->first();

        $detailResponse = $this->actingAs($this->user)->get(route('attendance.show', ['id' => $correctionRequest->attendance_id]));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('*承認待ちのため修正はできません');
    }

}
