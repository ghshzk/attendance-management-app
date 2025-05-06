<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceStatus;

class AttendanceStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            '勤務外',
            '出勤中',
            '休憩中',
            '退勤済'
        ];

        foreach ($params as $status) {
            AttendanceStatus::create([
                'status' => $status,
            ]);
        }
    }
}
