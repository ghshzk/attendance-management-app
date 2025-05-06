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

class AttendanceStatusTest extends TestCase
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

    //勤務外の場合、勤怠ステータスが正しく表示される
    public function test_status_pending()
    {
        $response= $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    //出勤中の場合、勤怠ステータスが正しく表示される
    public function test_status_working()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'attendance_status_id' => $this->statuses['出勤中'],
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    //休憩中の場合、勤怠ステータスが正しく表示される
    public function test_status_break()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'attendance_status_id' => $this->statuses['休憩中'],
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    //退勤済の場合、勤怠ステータスが正しく表示される
    public function test_status_clock_out()
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
        $response->assertSee('退勤済');
    }
}
