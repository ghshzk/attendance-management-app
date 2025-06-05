<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

        return view('admin.staff_attendance_list', compact('staff', 'month','attendances'));
    }

    public function exportCsv($id, Request $request)
    {
        $staff = User::findOrFail($id);

        $month = $request->input('month')
            ? Carbon::parse($request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$month, $month->copy()->endOfMonth()])
            ->get();

        $fileName = "{$staff->name}_勤怠一覧_{$month->format('Y_m')}.csv";

        return response()->streamDownload(function () use ($attendances) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date,
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $attendance->break_time ?? '-',
                    $attendance->work_time
                ]);
            }

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
