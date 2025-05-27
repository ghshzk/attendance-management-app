<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;


class StaffListController extends Controller
{
    public function index()
    {
        $staffs = User::where('role', 'user')->get();
        return view('admin.staff_list', compact('staffs'));
    }

    public function staffIndex(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();

        $attendances = Attendance::with('breakTimes')
                        ->where('user_id', $id)
                        ->whereYear('date', $month->year)
                        ->whereMonth('date', $month->month) //月の絞り込み
                        ->orderBy('date') //日付順で表示
                        ->get();

        $attendances = $attendances->map(function ($attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $workTime = Carbon::parse($attendance->clock_in)
                            ->diffInMinutes(Carbon::parse($attendance->clock_out));
            } else {
                $workTime = null;
            }

            $breakTime = $attendance->breakTimes->reduce(function ($carry, $break) {
                if ($break->break_start && $break->break_end) {
                    return $carry + Carbon::parse($break->break_start)
                    ->diffInMinutes(Carbon::parse($break->break_end));
                }
                return $carry;
            }, 0);

            $actualWorkTime = $workTime !== null ? $workTime - $breakTime : null;

            $attendance->work_time = $actualWorkTime !== null
                ? sprintf('%d:%02d', floor($actualWorkTime / 60), $actualWorkTime % 60) : null;

            $attendance->break_time = sprintf('%d:%02d', floor($breakTime / 60), $breakTime % 60);

            return $attendance;
        });

        return view('admin.staff_attendance_list', compact('staff', 'month','attendances'));
    }

}
