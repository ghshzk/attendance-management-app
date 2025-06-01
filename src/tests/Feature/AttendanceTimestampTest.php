<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceTimestampTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    //現在の日時情報がUIと同じ形式で出力されている
    public function test_current_datetime()
    {
        Carbon::setTestNow(Carbon::now());

        $this->seed(\Database\Seeders\UsersTableSeeder::class);
        $user = User::where('email', 'user1@example.com')->first();
        $response = $this->actingAs($user)->get('/attendance');

        $now = Carbon::now();
        $expectedDate = $now->format('Y年n月j日');
        $expectedDayOfWeek = ['日','月','火','水','木','金','土'][$now->dayOfWeek];

        $response->assertSeeText($expectedDate)
                ->assertSeeText($expectedDayOfWeek);
    }
}
