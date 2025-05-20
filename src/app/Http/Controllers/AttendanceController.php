<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    //一般ユーザー
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y年n月j日');
        $dayOfWeek = ['日','月','火','水','木','金','土'][Carbon::now()->dayOfWeek];

        $attendance = Attendance::where('user_id', $user->id)
                        ->whereDate('date',Carbon::today())
                        ->first();

        $status = '勤務外';
        if ($attendance) {
            if ($attendance->clock_out) {
                $status = '退勤済';
            } elseif ($attendance->breakTimes()->whereNull('break_end')->exists()) {
                $status = '休憩中';
            } elseif ($attendance->clock_in) {
                $status = '勤務中';
            }
        }

        return view('attendance', compact('status', 'attendance', 'today', 'dayOfWeek'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now(),
            'attendance_status_id' => 2,
        ]);

        return redirect()->route('attendance.create');
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
                        ->whereDate('date', Carbon::today())
                        ->first();

        $attendance->breakTimes()->create([
            'break_start' => Carbon::now(),
        ]);

        $attendance->update([
            'attendance_status_id' => 3,
        ]);

        return redirect()->route('attendance.create');
    }


    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
                        ->whereDate('date', Carbon::today())
                        ->firstOrFail();

        $break = BreakTime::where('attendance_id', $attendance->id)
                        ->whereNull('break_end')
                        ->latest('id')
                        ->firstOrFail();

        $break->update([
            'break_end' => Carbon::now(),
        ]);

        $attendance->update([
            'attendance_status_id' => 2,
        ]);

        return redirect()->route('attendance.create');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())
                        ->whereDate('date', Carbon::today())
                        ->first();

        $attendance->update([
            'clock_out' => Carbon::now(),
            'attendance_status_id' => 4,
        ]);

        return redirect()->route('attendance.create');
    }


    //管理者ユーザー表示
    public function adminIndex()
    {
        return view('admin.attendance_list');
    }

}
