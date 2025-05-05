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

        $status = 'pending';
        if ($attendance) {
            if ($attendance->clock_out) {
                $status = 'clocked_out';
            } elseif ($attendance->breakTimes()->whereNull('break_end')->exists()) {
                $status = 'break';
            } elseif ($attendance->clock_in) {
                $status = 'working';
            }
        }

        return view('attendance', compact('status', 'attendance', 'today', 'dayOfWeek'));
    }



    //管理者ユーザー表示
    public function adminIndex()
    {
        return view('admin.attendance_list');
    }

}
