<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreakTime;
use App\Http\Controllers\AdminCorrectionRequestController;


class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        //管理者の場合は別コントローラで処理
        if (Auth::user()->role === 'admin') {
            return (new AdminCorrectionRequestController())->index($request);
        }

        $user = Auth::user();
        $status = $request->query('status', 'pending');

        //承認状態でデータ取得
        $query = CorrectionRequest::with(['user', 'attendance', 'correctionBreakTimes'])
                    ->where('user_id', $user->id);

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        //取得した結果を申請日で新しい順に並び替え
        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('correction_request', compact('requests', 'status'));
    }
}
