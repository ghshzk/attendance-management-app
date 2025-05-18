<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $attendance = Attendance::factory(10)->create();

        foreach ($attendance as $attendance) {
            BreakTime::factory(1)->forAttendance($attendance)->create();
        }
    }
}
