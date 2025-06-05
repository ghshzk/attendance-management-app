<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;


class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month') ? Carbon::parse($request->input('month')) : Carbon::now();

        $attendances = Attendance::with('breakTimes')
                        ->where('user_id', $user->id) //ログインユーザーのデータのみ取得
                        ->whereYear('date', $month->year)
                        ->whereMonth('date', $month->month) //月の絞り込み
                        ->orderBy('date') //日付順で表示
                        ->get();

        return view('attendance_list', compact('month','attendances'));
    }

    public function show($id) {
        $attendance = Attendance::with('user', 'breakTimes', 'correctionRequests.correctionBreakTimes')->findOrFail($id);

        $pendingCorrection = $attendance->correctionRequests()->where('status', 'pending')->latest()->first();

        return view('attendance_show', compact('attendance', 'pendingCorrection'));
    }

    public function update(AttendanceRequest $request, $id)
    {
        $validated = $request->validated();

        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $correctionRequest = CorrectionRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'remark' => $validated['remark'],
            'status' => 'pending',
        ]);

        //休憩時間の修正がある場合
        if (!empty($validated['break_start']) && !empty($validated['break_end'])) {
            foreach ($validated['break_start'] as $index => $start) {
                if (empty($start) && empty($validated['break_end'][$index])) {
                    continue;
                }

                $createData = [
                    'correction_request_id' => $correctionRequest->id,
                    'break_start' => $start,
                    'break_end' => $validated['break_end'][$index],
                ];

                if (isset($attendance->breakTimes[$index])) {
                    $createData['break_time_id'] = $attendance->breakTimes[$index]->id;
                }

                CorrectionBreakTime::create($createData);
            }
        } else {
            //休憩の修正がない場合は既存データ引き継ぎ
            foreach ($attendance->breakTimes as $breakTime) {
                CorrectionBreakTime::create([
                    'correction_request_id' => $correctionRequest->id,
                    'break_time_id' => $breakTime->id,
                    'break_start' => $breakTime->break_start,
                    'break_end' => $breakTime->break_end,
                ]);
            }
        }

        return redirect()->route('attendance.show', $attendance->id);
    }
}
