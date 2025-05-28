<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;

class AdminCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        //承認状態でデータ取得
        $query = CorrectionRequest::with(['user', 'attendance', 'correctionBreakTimes']);

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        //取得した結果を申請日で新しい順に並び替え
        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('admin.correction_request', compact('requests', 'status'));
    }

    public function show(CorrectionRequest $attendance_correct_request)
    {
        //$attendance_correct_request->load('correctionBreakTimes');
        $attendance = $attendance_correct_request->attendance;
        $pendingCorrection = $attendance_correct_request;

        return view('admin.approve', compact('pendingCorrection', 'attendance'));
    }

    public function update(Request $request, CorrectionRequest $attendance_correct_request)
    {
        if ($attendance_correct_request->status === 'approved') {
            return redirect();
        }

        DB::transaction(function() use ($attendance_correct_request) {
            $attendance = $attendance_correct_request->attendance;

            $attendance->clock_in = $attendance_correct_request->clock_in;
            $attendance->clock_out = $attendance_correct_request->clock_out;
            $attendance->remark = $attendance_correct_request->remark;
            $attendance->save();

            if ($attendance_correct_request->correctionBreakTimes->isNotEmpty()) {
                $attendance->breakTimes()->delete();
                foreach ($attendance_correct_request->correctionBreakTimes as $break) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break->break_start,
                        'break_end' => $break->break_end,
                    ]);
                }
            }

            $attendance_correct_request->status = 'approved';
            $attendance_correct_request->save();
        });

        return redirect()->route('admin.approve.show', ['attendance_correct_request' => $attendance_correct_request->id]);
    }

}
