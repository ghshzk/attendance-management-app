<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;


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

Route::get('/attendance',[AttendanceController::class,'index']);


Route::get('/admin/login',[AdminLoginController::class,'create']);
Route::post('/admin/login',[AdminLoginController::class,'store']);
Route::post('/admin/logout',[AdminLoginController::class,'destroy'])->name('admin.logout');

Route::middleware('auth')->group(function(){
    Route::get('/admin/attendance/list',[AttendanceController::class,'adminIndex']);
});