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

class ClockOutTest extends TestCase
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

    //退勤ボタンが正しく機能する
    public function test_clock_out_button_works()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'attendance_status_id' => $this->statuses['出勤中'],
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('退勤');

        $this->post(route('attendance.clockOut'));

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    //退勤時刻が管理画面で確認できる、一旦保留
}
