<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
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

        return view('admin.attendance_list', compact('attendances', 'date'));
    }

    public function show($id) {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $attendance_correct_request = CorrectionRequest::where('attendance_id', $attendance->id)
                                    ->latest()
                                    ->first();

        if ($attendance_correct_request) {
            $attendance_correct_request->load('correctionBreakTimes');
        }
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
