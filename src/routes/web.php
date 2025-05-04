<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\RegisteredUserController;
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

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->name('verification.verify');

Route::get('/admin/login',[AdminLoginController::class,'create']);
Route::post('/admin/login',[AdminLoginController::class,'store']);
Route::post('/admin/logout',[AdminLoginController::class,'destroy'])->name('admin.logout');

Route::middleware('auth')->group(function(){
    Route::get('/admin/attendance/list',[AttendanceController::class,'adminIndex']);
});

Route::get('/attendance',[AttendanceController::class,'index']);
