<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminLoginController extends Controller
{
    public function create()
    {
        return view('admin.login');
    }


    public function store(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin'){
                return redirect('/admin/attendance/list');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => '管理者ではありません'
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
