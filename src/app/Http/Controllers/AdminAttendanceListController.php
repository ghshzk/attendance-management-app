<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;

class AdminAttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();

        $attendances = Attendance::with(['user', 'breakTimes'])
                        ->whereDate('date', $date)
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

        return view('admin.attendance_list', compact('attendances', 'date'));
    }

    public function show($id) {
        $attendance_correct_request->load('correctionBreakTimes');
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);
        return view('admin.attendance_show', compact('attendance'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $attendance->clock_in = $validated['clock_in'];
        $attendance->clock_out = $validated['clock_out'];
        $attendance->remark = $validated['remark'];
        $attendance->save();

        $attendance->breakTimes()->delete();

        if (isset($validated['break_start'])) {
            foreach ($validated['break_start'] as $index => $start) {
                $end = $validated['break_end'][$index] ?? null;
                if ($start || $end) {
                    $attendance->breakTimes()->create([
                        'break_start' => $start,
                        'break_end' => $end,
                    ]);
                }
            }
        }
        return redirect()->route('admin.attendance.show', ['id' => $id]);
    }
}
