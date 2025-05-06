<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceStatus;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendanceStatusesTableSeeder;

class BreakTest extends TestCase
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

        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'attendance_status_id' => $this->statuses['出勤中']
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */

    //休憩ボタンが正しく機能する
    public function test_break_start_button_works()
    {
        $response =  $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->post(route('attendance.breakStart')); //休憩処理

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->whereDate('date', Carbon::today())
                        ->first();

        $break = BreakTime::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break->break_start);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    //休憩は1日に何回でもできる
    public function test_multiple_break_start_records()
    {
        $response =  $this->actingAs($this->user)->get('/attendance');

        $this->post(route('attendance.breakStart'));
        $this->post(route('attendance.breakEnd'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    //休憩戻ボタンが正しく機能する
    public function test_break_end_button_works()
    {
        $response =  $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->post(route('attendance.breakStart')); //休憩処理

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->whereDate('date', Carbon::today())
                        ->first();

        $break = BreakTime::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break->break_start);

        $this->post(route('attendance.breakEnd'));

        $break->refresh();
        $this->assertNotNull($break->break_end);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    //休憩戻は1日に何回でもできる
    public function test_multiple_break_end_records()
    {
        $response =  $this->actingAs($this->user)->get('/attendance');

        $this->post(route('attendance.breakStart'));
        $this->post(route('attendance.breakEnd'));

        $this->post(route('attendance.breakStart'));

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    //休憩時刻が勤怠一覧画面で確認できる、一旦保留
}
