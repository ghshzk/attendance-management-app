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
            ['id' => 1, 'status' => '勤務外'],
            ['id' => 2, 'status' => '勤務中'],
            ['id' => 3, 'status' => '休憩中'],
            ['id' => 4, 'status' => '退勤済'],
        ];

        foreach ($params as $param) {
            AttendanceStatus::create([
                'id' => $param['id'],
                'status' => $param['status'],
            ]);
        }
    }
}
