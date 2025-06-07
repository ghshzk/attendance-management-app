<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
        $this->call([
            UsersTableSeeder::class,
            AttendanceStatusesTableSeeder::class,
        ]);

        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            $thisMonthDates = collect();
            $startOfMonth = now()->startOfMonth();

            for ($i = 0; $i < now()->day -1; $i++) {
                $thisMonthDates->push($startOfMonth->copy()->addDays($i)->format('Y-m-d'));
            }

            $lastMonth = now()->subMonth();
            $lastMonthDates = collect();
            $startOfLastMonth = $lastMonth->startOfMonth();
            for ($i = 0; $i < $lastMonth->daysInMonth; $i++) {
                $lastMonthDates->push($startOfLastMonth->copy()->addDays($i)->format('Y-m-d'));
            }

            $selectedThisMonthDates = $thisMonthDates->shuffle()->take(10);
            $selectedLastMonthDates = $lastMonthDates->shuffle()->take(10);

            foreach ($selectedThisMonthDates as $date) {
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date,
                ]);
                BreakTime::factory()->forAttendance($attendance)->create();
            }

            foreach ($selectedLastMonthDates as $date) {
                $attendance = Attendance::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date,
                ]);
                BreakTime::factory()->forAttendance($attendance)->create();
            }
        }
    }
}