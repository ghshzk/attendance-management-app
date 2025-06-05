<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceListController;
use App\Http\Controllers\AdminCorrectionRequestController;
use App\Http\Controllers\StaffListController;
use App\Http\Requests\EmailVerificationRequest;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/register',[RegisteredUserController::class,'store']);

Route::get('/email/verify', function () {
    return view('verify_email');
})->name('verification.notice');

//メール認証リンクの再送
Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

//メール認証リンクで認証後、勤怠登録画面へ遷移
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->name('verification.verify');

Route::get('/admin/login',[AdminLoginController::class,'create']);
Route::post('/admin/login',[AdminLoginController::class,'store']);
Route::post('/admin/logout',[AdminLoginController::class,'destroy'])->name('admin.logout');

Route::middleware(['auth'])->group(function(){
    //管理者ユーザー
    Route::get('/admin/attendance/list', [AdminAttendanceListController::class,'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceListController::class,'show'])->name('admin.attendance.show');
    Route::post('/admin/attendance/{id}', [AdminAttendanceListController::class,'update'])->name('admin.attendance.update');

    Route::get('/admin/staff/list', [StaffListController::class,'index'])->name('admin.staff.index');
    Route::get('/admin/staff/{id}', [StaffListController::class,'staffIndex'])->name('admin.staff.attendance');
    Route::get('/admin/staff/{id}/csv', [StaffListController::class,'exportCsv'])->name('admin.staff.attendance.csv');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'show'])->name('admin.approve.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'update'])->name('admin.approve.update');


    //一般ユーザー
    Route::get('/attendance', [AttendanceController::class,'create'])->name('attendance.create');
    Route::post('/attendance/clock_in', [AttendanceController::class,'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/break_start', [AttendanceController::class,'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break_end', [AttendanceController::class,'breakEnd'])->name('attendance.breakEnd');
    Route::post('/attendance/clock_put', [AttendanceController::class,'clockOut'])->name('attendance.clockOut');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [AttendanceListController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}', [AttendanceListController::class, 'update'])->name('attendance.update');

    //一般ユーザー・管理者ユーザー共通
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('correction.index');
});

